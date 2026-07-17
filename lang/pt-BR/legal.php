<?php

declare(strict_types=1);

return [
    'last_updated' => 'Última atualização: :date',
    'back_home' => 'Voltar à página inicial',
    'contact' => 'Dúvidas? Entre em contato em :email.',
    'terms_of_service' => 'Termos de Serviço',
    'privacy_policy' => 'Política de Privacidade',

    'terms' => [
        'page_title' => 'PandaPost Termos de Serviço',
        'title' => 'PandaPost Termos de Serviço',
        'intro' => 'Estes Termos de Serviço ("Termos") regem seu acesso e uso do PandaPost ("o Serviço", "nós"). Ao criar uma conta ou usar o Serviço, você concorda com estes Termos. Se você não concorda, não use o Serviço.',

        'sections' => [
            [
                'heading' => '1. Elegibilidade',
                'body' => 'Você deve ter ao menos 16 anos para usar o PandaPost. Ao usar o Serviço, você declara que atende a esse requisito e que tem autoridade para vincular qualquer organização em nome da qual atue.',
            ],
            [
                'heading' => '2. Conta e segurança',
                'body' => 'Você é responsável por proteger suas credenciais e por toda atividade realizada em sua conta. Notifique-nos imediatamente em caso de acesso não autorizado. Podemos suspender ou encerrar contas que violem estes Termos ou a legislação aplicável.',
            ],
            [
                'heading' => '3. Conexão de contas de redes sociais',
                'body' => 'O PandaPost permite conectar contas de plataformas terceiras (TikTok, Meta/Facebook/Instagram/Threads, X, LinkedIn, YouTube, Pinterest, Bluesky, Mastodon, Google Drive, entre outras) via OAuth. Você autoriza o PandaPost a agir em seu nome apenas dentro dos escopos que conceder explicitamente. Você pode desconectar qualquer conta a qualquer momento nas configurações do workspace, o que revoga os tokens armazenados.',
            ],
            [
                'heading' => '4. Seu conteúdo',
                'body' => 'Você mantém integralmente a titularidade do conteúdo que envia ou agenda pelo PandaPost ("Seu Conteúdo"). Você concede ao PandaPost uma licença limitada e não exclusiva para armazenar, processar e transmitir Seu Conteúdo apenas para operar o Serviço — incluindo publicá-lo nas plataformas terceiras que você instruir. Você é o único responsável pelo Seu Conteúdo e pelo cumprimento dos termos de cada plataforma.',
            ],
            [
                'heading' => '5. Uso aceitável',
                'body' => 'Você concorda em não usar o PandaPost para publicar conteúdo ilícito, infrator, abusivo, enganoso ou que viole os termos de qualquer plataforma conectada. Podemos remover conteúdo ou encerrar contas que violem esta política.',
            ],
            [
                'heading' => '6. Assinaturas e cobranças',
                'body' => 'Planos pagos são cobrados via Stripe. Os valores são cobrados antecipadamente pelo ciclo de cobrança selecionado. Você pode cancelar a qualquer momento; o cancelamento entra em vigor ao final do ciclo atual e não há reembolso por períodos parciais, salvo quando exigido por lei.',
            ],
            [
                'heading' => '7. Funcionalidades de IA',
                'body' => 'O PandaPost oferece recursos com IA (redação, refinamento, tradução). As respostas são geradas por provedores de IA terceiros e podem conter imprecisões. Você é responsável por revisar as saídas antes de publicar.',
            ],
            [
                'heading' => '8. Disponibilidade do Serviço',
                'body' => 'Fornecemos o PandaPost "no estado em que se encontra" e "conforme disponível". Não garantimos operação ininterrupta. Plataformas terceiras podem alterar APIs ou limites de uso a qualquer momento, o que pode afetar a publicação.',
            ],
            [
                'heading' => '9. Limitação de responsabilidade',
                'body' => 'Na máxima extensão permitida por lei, o PandaPost não responde por danos indiretos, incidentais ou consequenciais, nem por perda de dados, lucros ou negócios decorrentes do uso do Serviço.',
            ],
            [
                'heading' => '10. Encerramento',
                'body' => 'Você pode excluir sua conta a qualquer momento nas configurações. Após a exclusão, seus dados pessoais são apagados ou anonimizados em até 30 dias, exceto quando a retenção for exigida por lei (por exemplo, registros financeiros).',
            ],
            [
                'heading' => '11. Alterações destes Termos',
                'body' => 'Podemos atualizar estes Termos periodicamente. Alterações relevantes serão comunicadas por e-mail ou aviso in-app com pelo menos 14 dias de antecedência. O uso continuado após a data efetiva configura aceitação.',
            ],
            [
                'heading' => '12. Lei aplicável',
                'body' => 'Estes Termos são regidos pelas leis do Brasil. Eventuais disputas serão resolvidas no foro do domicílio do usuário quando se aplicar o Código de Defesa do Consumidor, e, em outros casos, no foro de São Paulo, SP, Brasil.',
            ],
        ],
    ],

    'privacy' => [
        'page_title' => 'PandaPost Política de Privacidade',
        'title' => 'PandaPost Política de Privacidade',
        'intro' => 'Esta Política de Privacidade explica como o PandaPost ("nós") coleta, usa e protege seus dados pessoais. Cumprimos a Lei Geral de Proteção de Dados (LGPD) brasileira e o Regulamento Geral de Proteção de Dados (GDPR) da União Europeia quando aplicável.',

        'sections' => [
            [
                'heading' => '1. Dados que coletamos',
                'body' => 'Ao se cadastrar, coletamos seu nome, e-mail, senha (com hash), idioma e fuso horário preferidos. Se você faz login com Google ou GitHub, recebemos o e-mail do perfil e dados públicos desses provedores. Ao conectar uma plataforma (TikTok, Meta, X, LinkedIn, YouTube, Pinterest, Bluesky, Mastodon, Threads, Google Drive), armazenamos os tokens OAuth de acesso e refresh criptografados, além do ID da conta e nome de exibição para identificarmos qual conta está conectada.',
            ],
            [
                'heading' => '2. Conteúdo que você fornece',
                'body' => 'Armazenamos posts, legendas, arquivos de mídia e agendamentos que você criar. As mídias ficam em nosso storage e podem ser processadas (redimensionadas, transcodificadas) para atender aos requisitos de cada plataforma. Nunca usamos Seu Conteúdo para treinar modelos de IA.',
            ],
            [
                'heading' => '3. Uso de dados do TikTok',
                'body' => 'Ao conectar a sua conta do TikTok, o aplicativo acessa informações do perfil público (nome de usuário, nome de exibição, avatar) e métricas de vídeo (curtidas, visualizações, comentários) por meio do TikTok API OAuth oficial. Esses dados são usados exclusivamente para exibir estatísticas no painel do usuário e nunca são vendidos, compartilhados ou usados para publicidade. O aplicativo apenas publica vídeos agendados em nome do usuário. O usuário pode desconectar sua conta a qualquer momento nas configurações ou revogar o acesso diretamente por meio das configurações de sua conta do TikTok (Segurança e login -> Gerenciar acesso ao aplicativo).',
            ],
            [
                'heading' => '4. Pagamentos',
                'body' => 'Os pagamentos são processados pelo Stripe. Não armazenamos dados de cartão em nossos servidores — o Stripe os mantém em sua infraestrutura certificada PCI-DSS. Guardamos apenas um identificador de cliente Stripe e o status da assinatura.',
            ],
            [
                'heading' => '5. Cookies e analytics',
                'body' => 'Usamos cookies essenciais para autenticação e gestão de sessão. Podemos usar analytics que respeitam a privacidade (PostHog) para entender o uso agregado do produto. Não executamos rastreadores de publicidade de terceiros.',
            ],
            [
                'heading' => '6. E-mail',
                'body' => 'Enviamos e-mails transacionais (verificação de conta, convites, recibos de cobrança, alertas de segurança). Não enviamos e-mails de marketing sem opt-in explícito.',
            ],
            [
                'heading' => '7. Compartilhamento de dados',
                'body' => 'Compartilhamos dados apenas com: (a) as plataformas sociais que você conecta, exclusivamente para publicar o conteúdo que você instruir; (b) Stripe, para processamento de pagamentos; (c) provedores de infraestrutura (hospedagem, e-mail) atuando como operadores sob contrato; (d) autoridades quando legalmente exigido.',
            ],
            [
                'heading' => '8. Retenção de dados',
                'body' => 'Mantemos os dados da sua conta enquanto ela estiver ativa. Após a exclusão da conta, apagamos ou anonimizamos seus dados pessoais em até 30 dias, exceto quando a retenção for exigida por lei (por exemplo, notas fiscais por 5 anos).',
            ],
            [
                'heading' => '9. Seus direitos',
                'body' => 'Sob a LGPD e o GDPR, você tem direito de acessar, corrigir, exportar, restringir o tratamento ou excluir seus dados pessoais. Exerça esses direitos nas configurações da conta ou entrando em contato pelo e-mail abaixo.',
            ],
            [
                'heading' => '10. Segurança',
                'body' => 'Usamos HTTPS em todo o tráfego, criptografamos tokens OAuth e campos sensíveis em repouso, e seguimos boas práticas de segurança de aplicação. Nenhum sistema é perfeitamente seguro; use uma senha forte e única.',
            ],
            [
                'heading' => '11. Transferências internacionais',
                'body' => 'Nossos servidores e operadores podem estar fora do seu país. Quando transferimos dados entre fronteiras, utilizamos Cláusulas Contratuais Padrão ou salvaguardas equivalentes.',
            ],
            [
                'heading' => '12. Crianças',
                'body' => 'O PandaPost não se destina a menores de 16 anos. Não coletamos intencionalmente dados pessoais de menores de 16. Se acreditar que coletamos, entre em contato para que possamos excluí-los.',
            ],
            [
                'heading' => '13. Alterações nesta política',
                'body' => 'Podemos atualizar esta Política. Alterações relevantes serão comunicadas por e-mail ou aviso in-app com pelo menos 14 dias de antecedência.',
            ],
        ],
    ],
];
