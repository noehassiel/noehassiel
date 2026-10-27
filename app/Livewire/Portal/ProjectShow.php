<?php

namespace App\Livewire\Portal;

use App\Enums\PaymentMethod;
use App\Enums\ProjectStatus;
use App\Enums\RequirementStatus;
use App\Enums\TicketType;
use App\Jobs\CreateLinearIssueForTicket;
use App\Mail\RequirementUploadedMail;
use App\Mail\TicketOpenedMail;
use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\Proposal;
use App\Models\RequirementFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Laravel\Cashier\Checkout;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Throwable;

#[Layout('components.layouts.portal')]
class ProjectShow extends Component
{
    use WithFileUploads;

    public Project $project;

    public string $linkLabel = '';

    public string $linkUrl = '';

    public string $ticketType = 'change';

    public string $ticketTitle = '';

    public string $ticketDescription = '';

    /** @var array<int, array<int, TemporaryUploadedFile>> Pending uploads, keyed by requirement id */
    public array $uploads = [];

    /** Set to "listo" when Stripe sends the client back after paying the balance. */
    #[Url(as: 'pago')]
    public string $paymentReturn = '';

    public function mount(Project $project): void
    {
        $this->authorize('view', $project);

        $this->project = $project;
    }

    /**
     * The client shares a folder (Drive, Dropbox, OneDrive…) where they'll upload the project resources.
     */
    public function addLink(): void
    {
        $this->authorize('addLink', $this->project);

        $validated = $this->validate([
            'linkLabel' => ['required', 'string', 'max:255'],
            'linkUrl' => ['required', 'url:https', 'max:2048'],
        ], attributes: [
            'linkLabel' => __('portal.project.folder_name'),
            'linkUrl' => __('portal.project.folder_url'),
        ]);

        $this->project->links()->create([
            'label' => $validated['linkLabel'],
            'url' => $validated['linkUrl'],
            'added_by' => Auth::id(),
        ]);

        $this->reset('linkLabel', 'linkUrl');
    }

    public function removeLink(int $linkId): void
    {
        $link = $this->project->links()->findOrFail($linkId);

        $this->authorize('removeLink', [$this->project, $link]);

        $link->delete();
    }

    /**
     * The client says they uploaded an item to their shared folder.
     */
    public function markUploaded(int $requirementId): void
    {
        $requirement = $this->project->requirements()->findOrFail($requirementId);

        $this->authorize('markRequirementUploaded', [$this->project, $requirement]);

        $this->notifyUploaded($requirement);
    }

    /**
     * The client uploads the files right here instead of sharing a folder.
     */
    public function uploadFiles(int $requirementId): void
    {
        $requirement = $this->project->requirements()->findOrFail($requirementId);

        $this->authorize('markRequirementUploaded', [$this->project, $requirement]);

        abort_if($requirement->status === RequirementStatus::Received, 403);

        $this->validate([
            "uploads.{$requirementId}" => ['required', 'array', 'max:10'],
            "uploads.{$requirementId}.*" => ['file', 'max:51200'],
        ], attributes: [
            "uploads.{$requirementId}" => __('portal.project.files'),
            "uploads.{$requirementId}.*" => __('portal.project.file'),
        ]);

        foreach ($this->uploads[$requirementId] as $upload) {
            $requirement->files()->create([
                'uploaded_by' => Auth::id(),
                'path' => $upload->store("requirements/{$this->project->id}", RequirementFile::diskName()),
                'original_name' => $upload->getClientOriginalName(),
                'mime_type' => $upload->getMimeType(),
                'size' => $upload->getSize(),
            ]);
        }

        unset($this->uploads[$requirementId]);

        $this->notifyUploaded($requirement);
    }

    public function removeFile(int $fileId): void
    {
        $file = RequirementFile::query()
            ->whereHas('requirement', fn ($query) => $query->where('project_id', $this->project->id))
            ->findOrFail($fileId);

        $this->authorize('removeRequirementFile', [$this->project, $file]);

        $file->delete();
    }

    /**
     * Only a real change notifies the admin; double clicks, re-marks or extra uploads stay silent.
     */
    private function notifyUploaded(ProjectRequirement $requirement): void
    {
        if ($requirement->status !== RequirementStatus::Pending) {
            return;
        }

        $requirement->markUploadedByClient();

        Mail::to(config('app.admin_email'))->send(new RequirementUploadedMail($requirement, Auth::user()));
    }

    /**
     * The client reports a bug, asks for a change or a question; it goes to Linear and to the admin's inbox.
     */
    public function submitTicket(): void
    {
        $this->authorize('view', $this->project);

        $validated = $this->validate([
            'ticketType' => ['required', Rule::enum(TicketType::class)],
            'ticketTitle' => ['required', 'string', 'max:255'],
            'ticketDescription' => ['required', 'string', 'max:10000'],
        ], attributes: [
            'ticketTitle' => __('portal.tickets.title_field'),
            'ticketDescription' => __('portal.tickets.description_field'),
        ]);

        $ticket = $this->project->tickets()->create([
            'user_id' => Auth::id(),
            'type' => $validated['ticketType'],
            'title' => $validated['ticketTitle'],
            'description' => $validated['ticketDescription'],
        ]);

        Mail::to(config('app.admin_email'))->send(new TicketOpenedMail($ticket));
        CreateLinearIssueForTicket::dispatchAfterResponse($ticket);

        $this->reset('ticketTitle', 'ticketDescription');
        $this->ticketType = TicketType::Change->value;
    }

    public function chooseBalancePaymentMethod(string $method): void
    {
        $this->authorize('payBalance', $this->project);

        $proposal = $this->project->proposal;
        abort_unless($proposal?->isBalanceDue(), 403);

        $proposal->chooseBalancePaymentMethod(PaymentMethod::from($method));
    }

    /** Send the client to Stripe Checkout for the balance plus the card fee, with meses sin intereses when allowed. */
    public function payBalanceWithCard(): void
    {
        $this->authorize('payBalance', $this->project);

        $proposal = $this->project->proposal;
        abort_unless($proposal?->isBalanceDue(), 403);

        $charge = Proposal::cardCharge($proposal->balanceAmount());
        $installments = Proposal::allowsInstallments($charge['charge'], $proposal->currency)
            ? ['payment_method_options' => ['card' => ['installments' => ['enabled' => true]]]]
            : [];

        $checkout = Checkout::guest()->create([[
            'price_data' => [
                'currency' => strtolower($proposal->currency),
                'unit_amount' => (int) round($charge['charge'] * 100),
                'product_data' => [
                    'name' => "Liquidación · {$proposal->title}",
                    'description' => "Propuesta {$proposal->number}. Incluye comisión por pago con tarjeta.",
                ],
            ],
        ]], [
            'mode' => 'payment',
            'customer_email' => Auth::user()->email,
            'metadata' => ['proposal_id' => $proposal->id, 'payment' => 'balance'],
            'success_url' => route('portal.projects.show', $this->project).'?pago=listo',
            'cancel_url' => route('portal.projects.show', $this->project),
            ...$installments,
        ]);

        $proposal->forceFill([
            'balance_payment_method' => PaymentMethod::Card,
            'stripe_balance_checkout_session_id' => $checkout->id,
        ])->save();

        $this->redirect($checkout->url);
    }

    public function render(): View
    {
        $proposal = $this->project->proposal;

        // Back from Stripe: confirm with Stripe directly instead of waiting for the webhook
        if ($proposal && $this->paymentReturn === 'listo' && ! $proposal->balance_paid_at) {
            try {
                $proposal->syncStripeBalanceCheckout();
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $balanceCharge = $proposal?->isBalanceDue() ? Proposal::cardCharge($proposal->balanceAmount()) : null;

        $steps = array_values(array_filter(
            ProjectStatus::cases(),
            fn (ProjectStatus $status): bool => $status !== ProjectStatus::Paused,
        ));

        return view('livewire.portal.project-show', [
            'requirements' => $this->project->requirements()->with('files')->get(),
            'links' => $this->project->links()->get(),
            'tickets' => $this->project->tickets()->with('user')->get(),
            'steps' => $steps,
            'currentStep' => array_search($this->project->status, $steps, true),
            'proposal' => $proposal,
            'balanceCharge' => $balanceCharge,
            'allowsInstallments' => $balanceCharge && Proposal::allowsInstallments($balanceCharge['charge'], $proposal->currency),
            'canPay' => Auth::user()->can('payBalance', $this->project),
            'bank' => config('services.bank'),
        ])->title($this->project->name);
    }
}
