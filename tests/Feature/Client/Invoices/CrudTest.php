<?php

declare(strict_types=1);

use App\Livewire\Client\Invoices\Crud;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery\MockInterface;
use Resend\Contracts\Client as ResendClient;
use Resend\Email;
use Resend\Service\Email as ResendEmailService;

uses(RefreshDatabase::class);

function fakeResend(): ResendEmailService&MockInterface
{
    $emailsMock = Mockery::mock(ResendEmailService::class);
    $emailsMock->shouldReceive('send')
        ->andReturn(Email::from(['id' => 'fake-id']));

    $resendMock = Mockery::mock(ResendClient::class);
    $resendMock->shouldAllowMockingMethod('emails')
        ->shouldReceive('emails')
        ->andReturn($emailsMock);

    app()->instance('resend', $resendMock);

    return $emailsMock;
}

beforeEach(function () {
    $this->user = User::factory()->admin()->create();
    $this->client = Client::factory()->create(['email' => 'client@example.com']);
    Storage::fake('local');
    fakeResend();
});

test('invoice crud component renders on client show page', function () {
    $this->actingAs($this->user)
        ->get(route('clients.show', $this->client))
        ->assertOk()
        ->assertSeeLivewire(Crud::class);
});

test('invoice list is empty by default', function () {
    Livewire::actingAs($this->user)
        ->test(Crud::class, ['client' => $this->client])
        ->assertSee('Sin facturas enviadas');
});

test('shows existing invoices', function () {
    Invoice::factory()->create([
        'client_id' => $this->client->id,
        'number' => 'F-001',
        'notes' => 'Diseño web',
        'total' => 5800.00,
        'tax' => 800.00,
        'currency' => 'MXN',
        'payment_status' => 'pending',
        'payment_method' => 'transfer',
        'sent_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(Crud::class, ['client' => $this->client])
        ->assertSee('F-001')
        ->assertSee('Diseño web')
        ->assertSee('Enviada');
});

test('opens modal dispatch when openModal is called', function () {
    Livewire::actingAs($this->user)
        ->test(Crud::class, ['client' => $this->client])
        ->call('openModal')
        ->assertDispatched('open-modal');
});

test('closes modal dispatch when closeModal is called', function () {
    Livewire::actingAs($this->user)
        ->test(Crud::class, ['client' => $this->client])
        ->call('closeModal')
        ->assertDispatched('close-modal');
});

test('sendInvoice validates required fields', function () {
    Livewire::actingAs($this->user)
        ->test(Crud::class, ['client' => $this->client])
        ->set('issue_date', 'not-a-date')
        ->call('sendInvoice')
        ->assertHasErrors(['number', 'issue_date', 'notes', 'tax', 'total', 'zip']);
});

test('sendInvoice creates invoice, stores zip and sends via Resend', function () {
    $zipFile = UploadedFile::fake()->create('factura.zip', 100, 'application/zip');

    Livewire::actingAs($this->user)
        ->test(Crud::class, ['client' => $this->client])
        ->set('number', 'F-001')
        ->set('issue_date', '2026-03-01')
        ->set('notes', 'Desarrollo de sitio web')
        ->set('tax', 627.20)
        ->set('total', 4547.20)
        ->set('currency', 'MXN')
        ->set('zip', $zipFile)
        ->call('sendInvoice')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal');

    $invoice = Invoice::where('number', 'F-001')->first();
    expect($invoice)->not->toBeNull();
    expect($invoice->sent_at)->not->toBeNull();
    expect($invoice->zip_path)->not->toBeNull();

    Storage::disk('local')->assertExists($invoice->zip_path);
});

test('download route returns 403 without valid signature', function () {
    $invoice = Invoice::factory()->create([
        'client_id' => $this->client->id,
        'zip_path' => 'invoices/1/factura.zip',
    ]);

    $this->actingAs($this->user)
        ->get(route('invoices.download', $invoice))
        ->assertForbidden();
});
