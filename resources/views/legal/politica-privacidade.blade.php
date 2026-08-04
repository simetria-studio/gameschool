<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index,follow">
    <title>Política de Privacidade — Go Game School</title>
    <meta name="description" content="Política de Privacidade do aplicativo Go Game School, em conformidade com a LGPD e requisitos da Google Play Store.">
    <style>
        :root {
            --gs-primary: #F2B233;
            --gs-primary-hover: #D99A22;
            --gs-ink: #1A1A1A;
            --gs-text: #2C2C2C;
            --gs-muted: #5F5F5F;
            --gs-bg: #F7F5F1;
            --gs-card: #FFFFFF;
            --gs-border: rgba(26, 26, 26, 0.08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            background:
                radial-gradient(1200px 500px at 10% -10%, rgba(242, 178, 51, 0.18), transparent 60%),
                radial-gradient(900px 400px at 100% 0%, rgba(26, 26, 26, 0.06), transparent 55%),
                var(--gs-bg);
            color: var(--gs-text);
            line-height: 1.65;
        }
        .wrap {
            max-width: 760px;
            margin: 0 auto;
            padding: 2.5rem 1.25rem 4rem;
        }
        header {
            margin-bottom: 2.25rem;
            padding-bottom: 1.5rem;
            border-bottom: 3px solid var(--gs-primary);
        }
        .brand {
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-size: 0.85rem;
            color: var(--gs-ink);
            margin: 0 0 0.75rem;
        }
        h1 {
            font-size: clamp(1.75rem, 4vw, 2.35rem);
            line-height: 1.2;
            margin: 0 0 0.75rem;
            color: var(--gs-ink);
        }
        .meta {
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            font-size: 0.92rem;
            color: var(--gs-muted);
            margin: 0;
        }
        .card {
            background: var(--gs-card);
            border: 1px solid var(--gs-border);
            border-radius: 12px;
            padding: 1.35rem 1.4rem;
            margin: 1.5rem 0;
        }
        h2 {
            font-size: 1.25rem;
            margin: 2rem 0 0.75rem;
            color: var(--gs-ink);
        }
        h3 {
            font-size: 1.05rem;
            margin: 1.25rem 0 0.5rem;
            color: var(--gs-ink);
        }
        p, li { font-size: 1.02rem; }
        ul { padding-left: 1.2rem; }
        li { margin-bottom: 0.4rem; }
        a { color: #8A5A00; }
        a:hover { color: var(--gs-primary-hover); }
        .note {
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            font-size: 0.9rem;
            color: var(--gs-muted);
            background: rgba(242, 178, 51, 0.12);
            border-left: 4px solid var(--gs-primary);
            padding: 0.85rem 1rem;
            margin: 1.25rem 0;
        }
        footer {
            margin-top: 3rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--gs-border);
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            font-size: 0.88rem;
            color: var(--gs-muted);
        }
        strong { color: var(--gs-ink); }
    </style>
</head>
<body>
    <main class="wrap">
        <header>
            <p class="brand">Go Game School</p>
            <h1>Política de Privacidade</h1>
            <p class="meta">Última atualização: 4 de agosto de 2026</p>
        </header>

        <div class="card">
            <p style="margin:0">
                Esta Política de Privacidade descreve como o <strong>Go Game School</strong>
                (“nós”, “nosso” ou “Aplicativo”) coleta, usa, armazena e protege informações
                no aplicativo móvel e nos serviços relacionados, em conformidade com a
                <strong>Lei Geral de Proteção de Dados (Lei nº 13.709/2018 — LGPD)</strong>
                e com os requisitos da <strong>Google Play Store</strong>.
            </p>
        </div>

        <h2>1. Controlador dos dados</h2>
        <p>
            O controlador dos dados pessoais tratados por meio do Aplicativo é o
            <strong>Go Game School</strong>. Para questões de privacidade:
        </p>
        <ul>
            <li><strong>E-mail de privacidade:</strong> <a href="mailto:privacidade@gogameschool.com.br">privacidade@gogameschool.com.br</a></li>
            <li><strong>Site / painel:</strong> {{ rtrim(config('app.url'), '/') }}</li>
        </ul>

        <h2>2. A quem se destina o Aplicativo</h2>
        <p>
            O Go Game School é uma plataforma educacional gamificada usada por escolas e unidades
            de ensino. Destina-se principalmente a <strong>alunos</strong> (incluindo crianças e
            adolescentes), bem como a professores, direção e administradores autorizados pela escola.
        </p>
        <p>
            Contas de alunos são, em regra, <strong>criadas e gerenciadas pela escola</strong>
            (ou unidade de ensino), e não por cadastro aberto ao público em geral.
        </p>

        <h2>3. Dados que coletamos</h2>
        <p>Dependendo do perfil de uso, podemos tratar as seguintes categorias de dados:</p>

        <h3>3.1 Dados de conta e identificação</h3>
        <ul>
            <li>Nome</li>
            <li>Nome de usuário (username)</li>
            <li>E-mail (quando cadastrado, especialmente para staff/administradores)</li>
            <li>Senha (armazenada de forma criptografada/hash)</li>
            <li>Token de login por QR Code do crachá (quando aplicável)</li>
            <li>Perfil de acesso (aluno, professor, direção, master etc.)</li>
            <li>Unidade de ensino e turma vinculadas</li>
        </ul>

        <h3>3.2 Dados do aluno e progresso educacional/gamificado</h3>
        <ul>
            <li>Gênero (quando informado)</li>
            <li>Data de nascimento (quando informada)</li>
            <li>Saldo de moedas virtuais (coins), experiência (XP) e ranking</li>
            <li>Participação em missões, quizzes, tentativas e respostas</li>
            <li>Pedidos na loja virtual, inventário, figurinhas, presentes e giros de roleta</li>
            <li>Configuração de avatar / peças desbloqueadas e equipadas</li>
            <li>Notificações internas do aplicativo relacionadas à conta</li>
        </ul>

        <h3>3.3 Dados técnicos do dispositivo e sessão</h3>
        <ul>
            <li>Identificador/nome do dispositivo associado ao token de autenticação (ex.: nome informado pelo app)</li>
            <li>Logs técnicos necessários à segurança e ao funcionamento (por exemplo, erros de autenticação e eventos de servidor)</li>
            <li>Endereço IP e dados de conexão, na medida em que forem registrados pela infraestrutura de hospedagem</li>
        </ul>

        <h3>3.4 Permissões do dispositivo (quando solicitadas pelo app)</h3>
        <ul>
            <li><strong>Câmera:</strong> apenas se o usuário optar por escanear o QR Code do crachá para login. Não usamos a câmera para gravar ou enviar imagens em segundo plano.</li>
            <li><strong>Internet:</strong> necessária para autenticação e sincronização com nossos servidores.</li>
        </ul>
        <p>
            Não solicitamos acesso a contatos, localização GPS contínua, microfone ou galeria
            como requisito padrão do serviço, salvo se uma funcionalidade futura exigir e for
            claramente informada no momento da solicitação da permissão.
        </p>

        <h2>4. Finalidades do tratamento</h2>
        <p>Tratamos os dados para:</p>
        <ul>
            <li>Autenticar o usuário e manter a sessão segura (login por senha ou QR Code)</li>
            <li>Operar a experiência educacional gamificada (missões, quizzes, ranking, loja, inventário, avatar etc.)</li>
            <li>Permitir que a escola/unidade acompanhe progresso e atividades pedagógicas vinculadas à plataforma</li>
            <li>Prestar suporte técnico e corrigir falhas</li>
            <li>Cumprir obrigações legais e regulatórias</li>
            <li>Proteger a segurança da conta, prevenir fraudes e abusos</li>
        </ul>

        <h2>5. Bases legais (LGPD)</h2>
        <p>O tratamento pode se apoiar, conforme o caso, em:</p>
        <ul>
            <li><strong>Execução de contrato / prestação do serviço</strong> solicitado pela escola ou usuário (art. 7º, V)</li>
            <li><strong>Legítimo interesse</strong>, quando aplicável e com salvaguardas, para segurança e melhoria do serviço (art. 7º, IX)</li>
            <li><strong>Cumprimento de obrigação legal ou regulatória</strong> (art. 7º, II)</li>
            <li><strong>Consentimento</strong>, quando exigido — inclusive do responsável legal no caso de crianças (art. 14 da LGPD)</li>
        </ul>

        <h2>6. Crianças e adolescentes</h2>
        <p>
            Como o Aplicativo pode ser usado por crianças e adolescentes, tratamos dados de menores
            com atenção especial às regras da LGPD e às políticas da Google Play para apps voltados
            a famílias / menores.
        </p>
        <ul>
            <li>O cadastro de alunos é, em geral, realizado pela escola/unidade com autorização dos responsáveis, conforme rotina da instituição.</li>
            <li>Não usamos dados de crianças para publicidade comportamental, remarketing ou venda de dados.</li>
            <li>Não exibimos anúncios personalizados de terceiros com base no perfil da criança.</li>
            <li>Pais, responsáveis ou a escola podem solicitar acesso, correção ou exclusão de dados do menor pelos canais desta Política.</li>
        </ul>

        <h2>7. Compartilhamento de dados</h2>
        <p>Podemos compartilhar dados apenas quando necessário, por exemplo com:</p>
        <ul>
            <li><strong>A escola / unidade de ensino</strong> à qual o aluno ou colaborador está vinculado, para fins pedagógicos e administrativos da plataforma</li>
            <li><strong>Prestadores de infraestrutura</strong> (hospedagem, e-mail transacional, monitoramento técnico), sob obrigação de confidencialidade e apenas para operar o serviço</li>
            <li><strong>Autoridades públicas</strong>, quando houver obrigação legal ou ordem válida</li>
        </ul>
        <p>
            <strong>Não vendemos</strong> dados pessoais. Não compartilhamos dados de alunos com anunciantes
            para fins de publicidade personalizada.
        </p>

        <h2>8. Armazenamento, retenção e segurança</h2>
        <ul>
            <li>Os dados são armazenados em servidores sob nosso controle ou de prestadores contratados.</li>
            <li>Senhas são armazenadas com hash; tokens de API podem ser revogados (logout).</li>
            <li>Mantemos medidas técnicas e administrativas razoáveis para proteger os dados contra acesso não autorizado, perda ou alteração indevida.</li>
            <li>Os dados são retidos enquanto a conta estiver ativa e/ou enquanto necessário para a prestação do serviço à escola, cumprimento legal, defesa de direitos ou resolução de disputas. Após o encerramento do vínculo, poderemos anonimizar ou excluir os dados, salvo retenção legal.</li>
        </ul>

        <h2>9. Transferência internacional</h2>
        <p>
            Caso prestadores de infraestrutura estejam localizados fora do Brasil, adotaremos
            salvaguardas adequadas previstas na LGPD para a transferência internacional de dados,
            quando aplicável.
        </p>

        <h2>10. Seus direitos</h2>
        <p>Nos termos da LGPD, o titular (ou responsável legal) pode solicitar:</p>
        <ul>
            <li>Confirmação da existência de tratamento</li>
            <li>Acesso aos dados</li>
            <li>Correção de dados incompletos, inexatos ou desatualizados</li>
            <li>Anonimização, bloqueio ou eliminação de dados desnecessários ou excessivos</li>
            <li>Portabilidade, quando aplicável</li>
            <li>Informação sobre compartilhamentos</li>
            <li>Revogação do consentimento, quando o tratamento se basear nele</li>
            <li>Oposição a tratamentos em hipóteses legais</li>
        </ul>
        <p>
            Para exercer esses direitos, envie um e-mail para
            <a href="mailto:privacidade@gogameschool.com.br">privacidade@gogameschool.com.br</a>
            com identificação suficiente da conta/aluno e da escola. Podemos solicitar confirmação
            de identidade ou autorização da escola/responsável antes de atender o pedido.
        </p>

        <h2>11. Serviços de terceiros e SDKs</h2>
        <p>
            O Aplicativo comunica-se com nossos servidores via API autenticada.
            Se no futuro integrarmos serviços de terceiros (por exemplo, analytics, crash reporting
            ou push notifications), atualizaremos esta Política e a ficha de segurança de dados
            da Play Store, indicando quais dados são coletados por esses serviços.
        </p>
        <p>
            No momento da publicação desta versão, o foco do tratamento é a operação própria do
            Go Game School (conta, progresso educacional/gamificado e funcionamento técnico).
        </p>

        <h2>12. Exclusão de conta e dados</h2>
        <p>
            Alunos e responsáveis devem, preferencialmente, solicitar a exclusão pela escola/unidade
            ou pelo e-mail de privacidade. Administradores autorizados também podem gerenciar contas
            pelo painel web, conforme permissões.
        </p>
        <p>
            Após a exclusão da conta, removeremos ou anonimizaremos os dados pessoais associados,
            salvo quando a retenção for necessária por obrigação legal ou legítimo interesse
            (ex.: registros mínimos de segurança).
        </p>

        <h2>13. Alterações nesta Política</h2>
        <p>
            Podemos atualizar esta Política periodicamente. A data de “Última atualização” no topo
            indica a versão vigente. Alterações relevantes poderão ser comunicadas no Aplicativo,
            no site ou por outros meios adequados.
        </p>

        <h2>14. Contato</h2>
        <p>
            Dúvidas sobre privacidade, segurança ou exercício de direitos:
        </p>
        <ul>
            <li>E-mail: <a href="mailto:privacidade@gogameschool.com.br">privacidade@gogameschool.com.br</a></li>
            <li>Controlador: Go Game School</li>
        </ul>
        <p>
            Você também pode apresentar reclamação à Autoridade Nacional de Proteção de Dados (ANPD),
            se entender que seus direitos não foram atendidos.
        </p>

        <footer>
            <p>© {{ date('Y') }} Go Game School. Todos os direitos reservados.</p>
            <p>URL desta página: <a href="{{ url('/politica-de-privacidade') }}">{{ url('/politica-de-privacidade') }}</a></p>
        </footer>
    </main>
</body>
</html>
