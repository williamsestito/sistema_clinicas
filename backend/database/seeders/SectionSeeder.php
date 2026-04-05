<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Section, SiteSetting, Testimonial, Tenant};

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            $this->command->error('Nenhum tenant encontrado. Execute o DatabaseSeeder primeiro.');
            return;
        }

        $tid = $tenant->id;

        /*
        |--------------------------------------------------------------------------
        | Seções do Site
        |--------------------------------------------------------------------------
        */
        $sections = [
            [
                'slug'     => 'hero',
                'title'    => 'Beleza e bem-estar começam pelos seus pés.',
                'content'  => 'Cuidado, saúde e estética em um só lugar.',
                'position' => 0,
            ],
            [
                'slug'     => 'sobre',
                'title'    => 'Sobre a Clínica Duda',
                'content'  => '<p>Com anos de experiência em podologia e estética, a <strong>Clínica Duda</strong> nasceu do sonho de oferecer um espaço acolhedor onde beleza e saúde caminham juntas.</p><p>Fundada por Duda, profissional dedicada e apaixonada pelo bem-estar de seus clientes, nossa clínica se tornou referência em Joinville pela excelência no atendimento e pela qualidade dos tratamentos oferecidos.</p><p>Acreditamos que cuidar de si mesmo é um ato de amor próprio. Por isso combinamos técnicas modernas com um atendimento humanizado, criando uma experiência única e transformadora.</p><p><strong>Seu bem-estar é nossa prioridade.</strong></p>',
                'position' => 1,
            ],
            [
                'slug'     => 'servicos',
                'title'    => 'Nossos Serviços',
                'content'  => 'Tratamentos especializados para sua beleza e bem-estar',
                'position' => 2,
            ],
            [
                'slug'     => 'profissionais',
                'title'    => 'Nossos Profissionais',
                'content'  => 'Equipe qualificada e dedicada ao seu bem-estar',
                'position' => 3,
            ],
            [
                'slug'     => 'depoimentos',
                'title'    => 'O Que Dizem Nossos Clientes',
                'content'  => 'Depoimentos reais de quem confia em nossos serviços',
                'position' => 4,
            ],
            [
                'slug'     => 'cta',
                'title'    => 'Pronta para se cuidar?',
                'content'  => 'Agende seu horário online com nossos profissionais e dê o primeiro passo rumo ao seu bem-estar.',
                'position' => 5,
            ],
            [
                'slug'     => 'rodape',
                'title'    => 'Rodapé / Informações de Contato',
                'content'  => "Clínica Duda — Sua clínica de podologia e estética em Joinville. Cuidado personalizado para sua saúde e beleza.\n\nEndereço: Rua das Flores, 123 - Centro, Joinville, SC - CEP 89201-000\nTelefone: (47) 99999-9999\nE-mail: contato@clinicaduda.com.br\n\nRedes Sociais: Instagram e Facebook\n\n© 2025 Clínica de Podologia e Estética Duda. Todos os direitos reservados.",
                'position' => 6,
            ],
        ];

        foreach ($sections as $s) {
            Section::updateOrCreate(
                ['tenant_id' => $tid, 'slug' => $s['slug']],
                array_merge($s, ['tenant_id' => $tid, 'active' => true])
            );
        }

        $this->command->info('Seções do site criadas: ' . count($sections));

        /*
        |--------------------------------------------------------------------------
        | Configurações do Site (SiteSetting)
        |--------------------------------------------------------------------------
        */
        SiteSetting::updateOrCreate(
            ['tenant_id' => $tid],
            [
                'site_title'    => 'Clínica Duda',
                'tagline'       => 'Beleza e bem-estar começam pelos seus pés.',
                'about_title'   => 'Sobre a Clínica Duda',
                'about_text'    => 'Com anos de experiência em podologia e estética, a Clínica Duda nasceu do sonho de oferecer um espaço acolhedor onde beleza e saúde caminham juntas. Fundada por Duda, profissional dedicada e apaixonada pelo bem-estar de seus clientes, nossa clínica se tornou referência em Joinville.',
                'contact_phone' => '(47) 99999-9999',
                'contact_email' => 'contato@clinicaduda.com.br',
                'address'       => 'Rua das Flores, 123 - Centro, Joinville, SC - CEP 89201-000',
                'instagram_url' => 'https://instagram.com/clinicaduda',
                'facebook_url'  => 'https://facebook.com/clinicaduda',
                'whatsapp_url'  => 'https://wa.me/5547999999999',
                'active'        => true,
            ]
        );

        $this->command->info('Configurações do site preenchidas.');

        /*
        |--------------------------------------------------------------------------
        | Depoimentos
        |--------------------------------------------------------------------------
        */
        $testimonials = [
            [
                'client_name' => 'Maria Silva',
                'rating'      => 5,
                'comment'     => 'Atendimento excepcional! A Duda é extremamente profissional e atenciosa. Meus pés nunca estiveram tão bem cuidados. Recomendo de olhos fechados!',
                'visible'     => true,
            ],
            [
                'client_name' => 'João Santos',
                'rating'      => 5,
                'comment'     => 'Ambiente acolhedor e serviços de primeira qualidade. Fiz o Spa dos Pés e saí renovado. Com certeza voltarei muitas vezes!',
                'visible'     => true,
            ],
            [
                'client_name' => 'Ana Paula',
                'rating'      => 5,
                'comment'     => 'A limpeza de pele foi maravilhosa! Profissionais competentes e produtos de alta qualidade. Minha pele ficou incrível após o tratamento.',
                'visible'     => true,
            ],
            [
                'client_name' => 'Carlos Mendes',
                'rating'      => 5,
                'comment'     => 'Tratamento podológico impecável. Senti alívio imediato após o procedimento. A equipe toda é muito atenciosa e qualificada.',
                'visible'     => true,
            ],
        ];

        foreach ($testimonials as $t) {
            Testimonial::updateOrCreate(
                ['tenant_id' => $tid, 'client_name' => $t['client_name']],
                array_merge($t, ['tenant_id' => $tid])
            );
        }

        $this->command->info('Depoimentos criados: ' . count($testimonials));
    }
}
