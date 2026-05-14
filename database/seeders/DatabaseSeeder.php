<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderComment;
use App\Models\ServiceOrderRead;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketRead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Categorias realistas de TI ────────────────────────────────────────
        $cats = collect([
            ['name' => 'Hardware',                  'color' => '#ef4444'],
            ['name' => 'Software e Sistemas',        'color' => '#3b82f6'],
            ['name' => 'Rede e Conectividade',       'color' => '#8b5cf6'],
            ['name' => 'Impressoras e Periféricos',  'color' => '#f59e0b'],
            ['name' => 'Acesso e Permissões',        'color' => '#ec4899'],
            ['name' => 'E-mail e Comunicação',       'color' => '#10b981'],
            ['name' => 'Segurança da Informação',    'color' => '#06b6d4'],
            ['name' => 'Outros',                     'color' => '#6b7280'],
        ])->map(fn ($d) => Category::create($d));

        // ── Admins ────────────────────────────────────────────────────────────
        $admin1 = User::create([
            'name'     => 'Admin Principal',
            'email'    => 'admin@demo.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);
        $admin2 = User::create([
            'name'     => 'Admin Backup',
            'email'    => 'admin2@demo.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // ── Técnicos ──────────────────────────────────────────────────────────
        $techNames = [
            'Carlos Mendes', 'Fernanda Lima', 'Ricardo Alves', 'Patrícia Costa', 'Bruno Souza',
        ];
        $tecnicos = collect();
        foreach ($techNames as $i => $nome) {
            $tecnicos->push(User::create([
                'name'     => $nome,
                'email'    => 'tecnico' . ($i + 1) . '@demo.com',
                'password' => Hash::make('password'),
                'role'     => 'technician',
            ]));
        }

        // ── Colaboradores ─────────────────────────────────────────────────────
        $collabNames = [
            'Ana Paula Ferreira', 'João Pedro Silva', 'Márcia Rodrigues', 'Lucas Oliveira',
            'Beatriz Santos', 'Rafael Pereira', 'Camila Nascimento', 'Thiago Carvalho',
            'Juliana Martins', 'Rodrigo Gomes', 'Letícia Araújo', 'Felipe Torres',
            'Natalia Barbosa', 'Diego Pinto', 'Alessandra Lima',
        ];
        $colaboradores = collect();
        foreach ($collabNames as $i => $nome) {
            $colaboradores->push(User::create([
                'name'     => $nome,
                'email'    => 'colaborador' . ($i + 1) . '@demo.com',
                'password' => Hash::make('password'),
                'role'     => 'collaborator',
            ]));
        }

        $todosTecnicos = $tecnicos->concat([$admin1, $admin2]);

        // ── Títulos realistas de chamados por categoria ───────────────────────
        $titulos = [
            'Hardware' => [
                'Computador não liga após queda de energia',
                'Tela com listras verticais no monitor',
                'Teclado com teclas travadas',
                'Mouse sem resposta após atualização',
                'HD com barulho estranho ao iniciar',
                'Fonte do computador queimando',
                'Bateria do notebook não carrega',
                'Câmera do notebook não é reconhecida',
            ],
            'Software e Sistemas' => [
                'Excel travando ao abrir planilhas grandes',
                'Sistema ERP não abre após atualização',
                'Erro ao instalar pacote Office 365',
                'Antivírus bloqueando aplicativo interno',
                'Windows Update causando lentidão',
                'Software de ponto eletrônico com erro',
                'Aplicativo de gestão exibe tela branca',
                'Licença do Adobe Acrobat expirada',
            ],
            'Rede e Conectividade' => [
                'Sem acesso à internet no setor financeiro',
                'VPN corporativa não conecta de casa',
                'Wi-Fi instável na sala de reuniões',
                'Impressora de rede sumiu do sistema',
                'Lentidão na rede no período da tarde',
                'Cabo de rede danificado na estação 12',
                'Switch do 3º andar sem resposta',
                'Acesso à pasta compartilhada negado',
            ],
            'Impressoras e Periféricos' => [
                'Impressora HP não imprime em cores',
                'Papel enroscando na impressora do RH',
                'Scanner não é detectado pelo Windows',
                'Nobreak desligando computador incorretamente',
                'Projetor da sala 3 sem sinal HDMI',
                'Impressora térmica emitindo código de erro',
            ],
            'Acesso e Permissões' => [
                'Usuário bloqueado após troca de senha',
                'Sem permissão para acessar pasta do projeto',
                'Preciso de acesso ao sistema de RH',
                'Conta de e-mail bloqueada por suspeita de invasão',
                'Novo colaborador sem acesso ao sistema',
                'Acesso ao servidor de arquivos negado',
            ],
            'E-mail e Comunicação' => [
                'E-mail corporativo não sincroniza no celular',
                'Outlook não envia anexos grandes',
                'Teams com eco no microfone durante reuniões',
                'Caixa de entrada do Outlook corrompida',
                'E-mails indo para spam dos destinatários',
                'Assinatura de e-mail desconfigurada',
            ],
            'Segurança da Informação' => [
                'Suspeita de phishing recebido por e-mail',
                'Arquivo criptografado por possível ransomware',
                'Pendrive desconhecido encontrado na empresa',
                'Acesso não autorizado detectado no sistema',
                'Usuário com permissões além do necessário',
            ],
            'Outros' => [
                'Solicitação de novo equipamento para estágio',
                'Dúvida sobre política de backup',
                'Pedido de suporte para evento presencial',
                'Configuração de segundo monitor na estação',
                'Solicitação de licença de software adicional',
            ],
        ];

        // ── Gerar chamados realistas (~190) nos últimos 90 dias ───────────────
        $totalTickets = 0;
        $ticketObjects = collect();

        foreach ($cats as $cat) {
            $catName = $cat->name;
            $titulosDisp = $titulos[$catName] ?? ["Suporte em {$catName}"];
            $qtd = match ($catName) {
                'Hardware', 'Software e Sistemas', 'Rede e Conectividade' => rand(28, 35),
                'Impressoras e Periféricos', 'Acesso e Permissões'        => rand(18, 24),
                'E-mail e Comunicação'                                     => rand(15, 20),
                'Segurança da Informação'                                  => rand(8, 12),
                default                                                    => rand(10, 15),
            };

            for ($i = 0; $i < $qtd; $i++) {
                $daysAgo   = rand(0, 90);
                $createdAt = Carbon::now()->subDays($daysAgo)->subHours(rand(0, 23))->subMinutes(rand(0, 59));
                $collab    = $colaboradores->random();
                $status    = $this->randomStatus($daysAgo);
                $technician = in_array($status, ['in_progress', 'resolved', 'closed', 'cancelled'])
                    ? $todosTecnicos->random()
                    : null;

                $resolvedAt = null;
                $closedAt   = null;
                if (in_array($status, ['resolved', 'closed'])) {
                    $resolvedAt = $createdAt->copy()->addHours(rand(2, 72));
                }
                if ($status === 'closed') {
                    $closedAt = $resolvedAt->copy()->addHours(rand(1, 48));
                }
                if ($status === 'cancelled') {
                    $closedAt = $createdAt->copy()->addHours(rand(1, 24));
                }

                $titulo = $titulosDisp[array_rand($titulosDisp)];
                if ($i >= count($titulosDisp)) {
                    $titulo .= ' #' . ($i - count($titulosDisp) + 2);
                }

                $ticket = Ticket::create([
                    'title'         => $titulo,
                    'description'   => $this->randomDescription($catName),
                    'status'        => $status,
                    'priority'      => $this->randomPriority(),
                    'user_id'       => $collab->id,
                    'category_id'   => $cat->id,
                    'technician_id' => $technician?->id,
                    'resolved_at'   => $resolvedAt,
                    'closed_at'     => $closedAt,
                    'created_at'    => $createdAt,
                    'updated_at'    => $resolvedAt ?? $createdAt->copy()->addMinutes(rand(0, 120)),
                ]);

                $ticketObjects->push($ticket);
                $totalTickets++;

                // Comentários em ~60% dos chamados
                if (rand(1, 10) <= 6) {
                    $numComments = rand(1, 4);
                    for ($c = 0; $c < $numComments; $c++) {
                        $commentAt = $createdAt->copy()->addHours(rand(1, 48));
                        $author    = rand(0, 1) ? $collab : ($technician ?? $todosTecnicos->random());
                        TicketComment::create([
                            'ticket_id'   => $ticket->id,
                            'user_id'     => $author->id,
                            'body'        => $this->randomComment($status),
                            'is_internal' => $technician && rand(0, 4) === 0,
                            'created_at'  => $commentAt,
                            'updated_at'  => $commentAt,
                        ]);
                    }
                }

                // Marcar como lido para chamados terminais (closed/cancelled) — demo não começa com 200 não-lidos
                if (in_array($status, ['closed', 'cancelled', 'resolved'])) {
                    TicketRead::updateOrCreate(
                        ['user_id' => $collab->id, 'ticket_id' => $ticket->id],
                        ['read_at' => now()]
                    );
                    if ($technician) {
                        TicketRead::updateOrCreate(
                            ['user_id' => $technician->id, 'ticket_id' => $ticket->id],
                            ['read_at' => now()]
                        );
                    }
                }
            }
        }

        // ── OS Internas (~40) ─────────────────────────────────────────────────
        $osTitulos = [
            'Instalação de novos computadores no setor de vendas',
            'Troca de switches no 2º andar',
            'Configuração de servidor de backup',
            'Revisão de cabeamento estruturado',
            'Instalação de câmeras de segurança',
            'Migração de dados para novo servidor',
            'Atualização de firmware dos roteadores',
            'Implantação de novo sistema de monitoramento',
            'Configuração de domínio para novos usuários',
            'Revisão de licenças de software',
            'Deploy de atualização no ERP',
            'Criação de VLANs para segmentação de rede',
            'Configuração de backup externo',
            'Instalação de no-breaks na sala de servidores',
            'Manutenção preventiva dos servidores',
            'Configuração de firewall para nova filial',
            'Integração do sistema de ponto com o AD',
            'Atualização de certificados SSL',
            'Renovação de contratos de suporte',
            'Inventário de equipamentos de TI',
        ];

        for ($i = 0; $i < 42; $i++) {
            $daysAgo   = rand(0, 60);
            $createdAt = Carbon::now()->subDays($daysAgo)->subHours(rand(0, 12));
            $pair      = $tecnicos->shuffle()->take(2);
            $requester = $pair->first();
            $assigned  = $pair->count() > 1 ? $pair->last() : $todosTecnicos->random();

            $status  = $this->randomOrderStatus($daysAgo);
            $doneAt  = ($status === 'done') ? $createdAt->copy()->addDays(rand(1, 5)) : null;

            $ordem = ServiceOrder::create([
                'title'          => $osTitulos[array_rand($osTitulos)] . ($i >= count($osTitulos) ? ' #' . $i : ''),
                'description'    => fake()->paragraph(2),
                'status'         => $status,
                'priority'       => $this->randomPriority(),
                'requester_id'   => $requester->id,
                'assigned_to_id' => $assigned->id,
                'done_at'        => $doneAt,
                'created_at'     => $createdAt,
                'updated_at'     => $doneAt ?? $createdAt->copy()->addHours(rand(0, 48)),
            ]);

            // Comentários em ~50% das OS
            if (rand(0, 1)) {
                for ($c = 0; $c < rand(1, 3); $c++) {
                    $commentAt = $createdAt->copy()->addHours(rand(1, 24));
                    $author    = rand(0, 1) ? $requester : $assigned;
                    ServiceOrderComment::create([
                        'service_order_id' => $ordem->id,
                        'user_id'          => $author->id,
                        'body'             => $this->randomComment($status),
                        'created_at'       => $commentAt,
                        'updated_at'       => $commentAt,
                    ]);
                }
            }

            // Marcar como lido para OS terminais
            if ($status === 'done') {
                ServiceOrderRead::updateOrCreate(
                    ['user_id' => $requester->id, 'service_order_id' => $ordem->id],
                    ['read_at' => now()]
                );
                ServiceOrderRead::updateOrCreate(
                    ['user_id' => $assigned->id, 'service_order_id' => $ordem->id],
                    ['read_at' => now()]
                );
            }
        }

        $this->command->info("✅ Seed concluído: {$totalTickets} chamados, 42 OS, 22 usuários, 8 categorias.");
        $this->command->line('');
        $this->command->line('  <fg=yellow>Credenciais de acesso:</fg=yellow>');
        $this->command->line('  admin@demo.com       → password  (Admin)');
        $this->command->line('  admin2@demo.com      → password  (Admin)');
        $this->command->line('  tecnico1@demo.com    → password  (Técnico)');
        $this->command->line('  tecnico2@demo.com    → password  (Técnico)');
        $this->command->line('  tecnico3@demo.com    → password  (Técnico)');
        $this->command->line('  tecnico4@demo.com    → password  (Técnico)');
        $this->command->line('  tecnico5@demo.com    → password  (Técnico)');
        $this->command->line('  colaborador1@demo.com → password (Colaborador)');
        $this->command->line('  ...até colaborador15@demo.com');
    }

    // ─── Helpers privados ────────────────────────────────────────────────────

    private function randomStatus(int $daysAgo): string
    {
        // Chamados mais antigos têm mais chance de estar resolvidos/fechados
        if ($daysAgo > 60) {
            return fake()->randomElement(['resolved', 'resolved', 'closed', 'closed', 'closed', 'cancelled']);
        }
        if ($daysAgo > 30) {
            return fake()->randomElement(['open', 'in_progress', 'resolved', 'closed', 'closed', 'cancelled']);
        }
        return fake()->randomElement(['open', 'open', 'in_progress', 'in_progress', 'resolved', 'closed']);
    }

    private function randomOrderStatus(int $daysAgo): string
    {
        if ($daysAgo > 30) {
            return fake()->randomElement(['done', 'done', 'done', 'in_progress']);
        }
        return fake()->randomElement(['pending', 'in_progress', 'in_progress', 'done']);
    }

    private function randomPriority(): string
    {
        return fake()->randomElement(['low', 'low', 'medium', 'medium', 'medium', 'high']);
    }

    private function randomDescription(string $catName): string
    {
        $descs = [
            'Hardware' => [
                'O equipamento apresentou falha após reinicialização. O usuário relatou que o problema ocorre de forma intermitente desde ontem.',
                'Falha identificada no hardware. Necessita avaliação técnica para diagnóstico e possível substituição de componente.',
            ],
            'Software e Sistemas' => [
                'O sistema apresenta erro ao tentar realizar a operação. Já tentamos reinstalar mas o problema persiste.',
                'Após a última atualização automática, o software parou de funcionar corretamente. Afetando produtividade da equipe.',
            ],
            'Rede e Conectividade' => [
                'Sem acesso à rede desde o início do expediente. Outros usuários do mesmo setor também estão com o problema.',
                'A conexão cai periodicamente ao longo do dia. Prejudica o acesso aos sistemas na nuvem.',
            ],
            '_fallback' => [
                'Detalhe do problema informado pelo usuário. Solicito análise e suporte para resolução o mais breve possível.',
                'Problema identificado durante o uso rotineiro. Já foi reiniciado o equipamento sem sucesso.',
            ],
        ];
        $pool = $descs[$catName] ?? $descs['_fallback'];
        return $pool[array_rand($pool)];
    }

    private function randomComment(string $status): string
    {
        $comments = [
            'Verificado o problema, iniciando diagnóstico.',
            'Solicitei acesso remoto ao usuário para investigar.',
            'Problema identificado, aguardando peça de reposição.',
            'Realizado procedimento de correção. Aguardando confirmação do usuário.',
            'Usuário confirmou que o problema foi resolvido.',
            'Testado e funcionando normalmente após intervenção.',
            'Chamado encaminhado para segundo nível de suporte.',
            'Aguardando retorno do fornecedor sobre a licença.',
            'Problema recorrente, vou documentar para acompanhamento.',
            'Usuário ausente, retornarei mais tarde.',
            'Backup realizado antes de qualquer alteração.',
            'Equipamento substituído temporariamente enquanto o original vai para manutenção.',
        ];
        return $comments[array_rand($comments)];
    }
}
