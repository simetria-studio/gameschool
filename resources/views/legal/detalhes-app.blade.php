<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index,follow">
    <title>Detalhes do App — Game School</title>
    <meta name="description" content="Conheça o aplicativo Game School: plataforma educacional gamificada com missões, quizzes, ranking, loja, avatar e muito mais.">
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
        .features {
            display: grid;
            gap: 0.85rem;
            margin: 1.5rem 0;
        }
        .feature {
            background: var(--gs-card);
            border: 1px solid var(--gs-border);
            border-radius: 12px;
            padding: 1rem 1.15rem;
        }
        .feature h3 {
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            font-size: 1rem;
            margin: 0 0 0.35rem;
            color: var(--gs-ink);
        }
        .feature p {
            margin: 0;
            font-size: 0.98rem;
            color: var(--gs-muted);
        }
        h2 {
            font-size: 1.25rem;
            margin: 2rem 0 0.75rem;
            color: var(--gs-ink);
        }
        p, li { font-size: 1.02rem; }
        ul { padding-left: 1.2rem; }
        li { margin-bottom: 0.4rem; }
        a { color: #8A5A00; }
        a:hover { color: var(--gs-primary-hover); }
        .links {
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem 1.25rem;
            margin-top: 1rem;
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
            <p class="brand">Game School</p>
            <h1>Detalhes do App</h1>
            <p class="meta">Plataforma educacional gamificada para escolas</p>
        </header>

        <div class="card">
            <p style="margin:0">
                O <strong>Game School</strong> é o aplicativo oficial da plataforma educacional
                gamificada usada por escolas e unidades de ensino. Alunos acompanham o progresso,
                cumprem missões, fazem quizzes, colecionam itens e personalizam o avatar —
                tudo em um ambiente seguro e gerenciado pela instituição.
            </p>
        </div>

        <h2>Para quem é</h2>
        <ul>
            <li><strong>Alunos</strong> — usam o app no dia a dia da escola</li>
            <li><strong>Professores, direção e administradores</strong> — gerenciam turmas, missões e conteúdo pelo painel web</li>
        </ul>
        <p>
            Contas de alunos são criadas e gerenciadas pela escola. O login pode ser feito
            por usuário e senha ou pelo QR Code do crachá.
        </p>

        <h2>Principais funcionalidades</h2>
        <div class="features">
            <div class="feature">
                <h3>Missões e atitudes</h3>
                <p>Atividades e comportamentos valorizados pela escola, com recompensas em coins e XP.</p>
            </div>
            <div class="feature">
                <h3>Quizzes</h3>
                <p>Questionários da turma, com tentativas, correção automática e histórico de desempenho.</p>
            </div>
            <div class="feature">
                <h3>Ranking</h3>
                <p>Classificação por coins ou XP, incentivando participação e engajamento.</p>
            </div>
            <div class="feature">
                <h3>Loja e pedidos</h3>
                <p>Troca de coins por itens da loja virtual da unidade, com acompanhamento de pedidos.</p>
            </div>
            <div class="feature">
                <h3>Roleta, figurinhas e inventário</h3>
                <p>Giros premiados, álbum de figurinhas, personagens, emotes e envio de presentes entre alunos.</p>
            </div>
            <div class="feature">
                <h3>Avatar</h3>
                <p>Personalização do personagem com peças desbloqueáveis (cabelo, roupa, acessórios e mais).</p>
            </div>
            <div class="feature">
                <h3>Login por crachá</h3>
                <p>Acesso rápido pelo QR Code do crachá do aluno (pode usar a câmera do celular).</p>
            </div>
        </div>

        <h2>Informações do aplicativo</h2>
        <ul>
            <li><strong>Nome:</strong> Game School</li>
            <li><strong>Categoria:</strong> Educação</li>
            <li><strong>Plataforma:</strong> Android (Google Play)</li>
            <li><strong>Acesso:</strong> Conta fornecida pela escola / unidade de ensino</li>
            <li><strong>Idioma:</strong> Português (Brasil)</li>
            <li><strong>Conteúdo:</strong> Voltado a uso escolar; sem anúncios publicitários personalizados</li>
        </ul>

        <h2>Permissões</h2>
        <ul>
            <li><strong>Internet</strong> — autenticação e sincronização com os servidores</li>
            <li><strong>Câmera</strong> — apenas se o usuário escolher escanear o QR Code do crachá</li>
        </ul>

        <h2>Suporte e privacidade</h2>
        <p>
            Dúvidas sobre o app: entre em contato com a sua escola/unidade ou pelo e-mail
            <a href="mailto:privacidade@gameschool.com.br">privacidade@gameschool.com.br</a>.
        </p>
        <div class="links">
            <a href="{{ route('legal.privacidade') }}">Política de Privacidade</a>
            <a href="{{ url('/detalhes-do-app') }}">Detalhes do App</a>
        </div>

        <footer>
            <p>© {{ date('Y') }} Game School. Todos os direitos reservados.</p>
            <p>URL desta página: <a href="{{ url('/detalhes-do-app') }}">{{ url('/detalhes-do-app') }}</a></p>
        </footer>
    </main>
</body>
</html>
