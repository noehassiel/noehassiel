<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Enums\PortalRole;
use App\Enums\ProjectStatus;
use App\Enums\ProposalProjectType;
use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Local demo of the preview review flow with the IICC website: client, portal user, the accepted proposal
 * and its project in development, previewing the iicc-web.test site served by Herd.
 * Written straight to the database so no mail, Stripe or Linear call goes out.
 *
 * php artisan db:seed --class=IiccPreviewDemoSeeder
 */
class IiccPreviewDemoSeeder extends Seeder
{
    public function run(): void
    {
        abort_if(app()->isProduction(), 403, 'Demo data is for local only.');

        DB::transaction(function (): void {
            $client = Client::query()->updateOrCreate(
                ['name' => 'Instituto de Idioma y Cultura China en México, A.C.'],
                [
                    'contact_name' => 'Dirección IICC',
                    'email' => 'demo-iicc@noehassiel.test',
                    'website' => 'https://iicc.org.mx',
                    'rfc' => null,
                    'phone' => null,
                    'address' => null,
                    'notes' => 'Datos de demo para probar la vista previa.',
                ],
            );

            $user = User::query()->updateOrCreate(
                ['email' => 'demo-iicc@noehassiel.test'],
                [
                    'name' => 'Dirección IICC',
                    'password' => 'password',
                    'client_id' => $client->id,
                    'portal_role' => PortalRole::Owner,
                    'locale' => 'es',
                    'email_verified_at' => now(),
                ],
            );

            $project = Project::query()->updateOrCreate(
                ['client_id' => $client->id, 'name' => 'Sitio web IICC'],
                [
                    'description' => 'El sitio del instituto, con los mismos menús, contenido más completo, diseño moderno y un panel para editarlo sin tocar código.',
                    'status' => ProjectStatus::Development,
                    'progress_note' => 'Ya está en línea la vista previa. Recórrela y pide los ajustes desde ahí.',
                    'starts_on' => now()->subWeeks(5),
                    'due_on' => now()->addWeek(),
                    'preview_url' => 'https://iicc-web.test',
                    'self_service_notes' => 'Desde su panel en /admin el instituto edita los banners del inicio (texto, botón, imagen o video y orden), los eventos con sus fotos y las reseñas de Google.',
                    'revision_rounds' => 2,
                ],
            );
            $project->forceFill(['current_round' => 1, 'approved_at' => null, 'approved_by' => null])->save();
            $project->previewMessages()->delete();
            $project->tickets()->whereNotNull('category')->delete();

            $proposal = Proposal::query()->updateOrCreate(
                ['number' => 'PROP-DEMO-IICC'],
                [
                    'client_id' => $client->id,
                    'lead_id' => null,
                    'title' => 'Sitio web IICC',
                    'project_type' => ProposalProjectType::WebDesignAndDevelopment,
                    'what_we_do' => 'Rediseñamos el sitio del Instituto de Idioma y Cultura China en México: los mismos menús que ya conocen sus alumnos, con contenido más completo, un diseño moderno y un panel para que el instituto lo actualice sin tocar código.',
                    'how_we_do_it' => 'Partimos del sitio actual y su estructura. Diseñamos cada página, la construimos en Laravel y la dejamos lista en español y chino tradicional. Sin venta en línea: cada llamado a la acción abre WhatsApp directo.',
                    'includes' => [
                        'Páginas: Inicio, Cursos, Certificaciones, Becas, Otros Servicios, Eventos, Nosotros y Contacto',
                        'Sitio en español y chino tradicional',
                        'Panel para editar el banner principal y los eventos',
                        'Reseñas de Google en el sitio',
                        'Botón de WhatsApp en cada llamado a la acción',
                        'Diseño adaptado a celular',
                    ],
                    'excludes' => [
                        'Venta o inscripción en línea',
                        'Blog',
                        'Cuentas de alumnos',
                        'Redacción de contenido nuevo (el instituto entrega los textos)',
                    ],
                    'notes' => 'Datos de demo para probar la vista previa.',
                    'status' => ProposalStatus::Paid,
                    'currency' => 'MXN',
                    'subtotal' => 38000,
                    'discount' => 0,
                    'tax_rate' => 0,
                    'total' => 38000,
                    'valid_until' => now()->addMonth(),
                    'sent_at' => now()->subWeeks(6),
                ],
            );
            $proposal->forceFill([
                'project_id' => $project->id,
                'payment_method' => PaymentMethod::Transfer,
                'deposit_paid_amount' => 19000,
                'deposit_paid_at' => now()->subWeeks(5),
                'balance_payment_method' => null,
                'balance_paid_amount' => null,
                'balance_paid_at' => null,
            ])->save();

            $proposal->stages()->delete();
            $proposal->items()->delete();

            foreach ([
                ['Diseño', 'Diseño de todas las páginas en escritorio y celular.', '2 semanas'],
                ['Desarrollo', 'Construcción del sitio, panel e idiomas.', '3 semanas'],
                ['Revisión y lanzamiento', 'Ajustes con el instituto y publicación en su hosting.', '1 semana'],
            ] as $order => [$name, $description, $duration]) {
                $proposal->stages()->create(compact('name', 'description', 'duration', 'order'));
            }

            foreach ([
                ['Diseño UI/UX de 8 páginas', 14000],
                ['Desarrollo en Laravel con español y chino tradicional', 18000],
                ['Panel de banners, eventos y reseñas', 6000],
            ] as $order => [$description, $price]) {
                $proposal->items()->create(['description' => $description, 'quantity' => 1, 'unit_price' => $price, 'subtotal' => $price, 'order' => $order]);
            }

            $this->command?->info("Portal: {$user->email} / password → ".route('portal.projects.preview', $project));
        });
    }
}
