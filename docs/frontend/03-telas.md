# 3. Telas

## 3.1 Mapa de rotas

| Rota | Tela | Acesso | Tipo de componente | Status em `pwa/` |
|---|---|---|---|---|
| `/` | T1: Explorar (mapa + lista) | público | Client | Parcial (`app/page.tsx`: mapa + filtros; falta lista e detalhe) |
| `/locais/[id]` | T2: Detalhe do local | público | Server (dados iniciais) + Client | A fazer |
| `/avaliar` | T3: Nova avaliação (assistente) | `user` | Client | A fazer |
| `/avaliar?placeId=…` ou `/locais/[id]/avaliar` | T3 com o local já escolhido | `user` | Client | A fazer |
| `/painel` | T4: Painel de dados | público | Client | A fazer |
| `/acessibilidade` | T6: Declaração de acessibilidade e preferências | público | Server + Client | A fazer |
| `/login` | T7: Login (redireciona ao Keycloak) | público | Client | Existe (trocar o callback padrão `/books` por `/`) |
| `/minhas-avaliacoes` | T8: Minhas avaliações | `user` | Client | **Bloqueada pela lacuna L3** |
| `/admin` | T9: Administração (React Admin) | `admin` | Client | Existe (esqueleto do demo; adaptar recursos) |
| `not-found`, `error` | T10: Erro / 404 / offline | — | — | A fazer |

Componentes globais em todas as telas (layout em `app/layout.tsx` e `components/common/Layout.tsx`):

- **Link "Pular para o conteúdo principal"**: o primeiro elemento focável.
- **Cabeçalho** (`<header>`): logo "Acessibiliza" (link para `/`), navegação principal (`<nav aria-label="Principal">`: Explorar, Painel, Sobre), botão **Avaliar um local** (destaque), botão **Acessibilidade** (abre o painel de preferências) e menu da conta (Entrar / nome do usuário, com "Sair").
- **Rodapé** (`<footer>`): IFSC Câmpus Tubarão, links para Sobre, Declaração de acessibilidade, código-fonte e contato para relatar problemas de acessibilidade do site.
- **Região de anúncios** (`<div aria-live="polite" class="sr-only">`): um anunciador global (ver 05-acessibilidade, §5.4.9).
- **Toaster** (`components/ui/toast.tsx`) com `role="status"`; para erros, `role="alert"`.
- **Widget VLibras** (ver 05-acessibilidade).

Em telas menores que `md` (768 px), a navegação vira um menu hambúrguer (`Sheet`) **ou** uma barra inferior com 4 itens: Explorar, Avaliar, Painel, Conta.

---

## T1: Explorar (`/`)

**Objetivo:** encontrar locais e ver rapidamente a situação de acessibilidade. Atende RF01–RF04.

### Layout

```
Desktop (≥ 1024 px)
┌──────────────────────────────────────────────────────────────────────────┐
│ [Pular para o conteúdo]  Acessibiliza   Explorar  Painel  [Avaliar] [Aa] [Entrar] │
├───────────────┬──────────────────────────────────────────────────────────┤
│ FILTROS       │  🔍 Buscar local ou endereço...        [ Mapa | Lista ]  │
│ Nome [_____]  │  "23 locais encontrados"  (aria-live)   Ordenar: [Nome▾] │
│ Endereço[___] │ ┌──────────────────────────────────────────────────────┐ │
│               │ │                                                      │ │
│ Acessibilidade│ │                MAPA (Google Maps)                    │ │
│ Cadeira rodas │ │         📍 pinos com ícone + cor por status          │ │
│ (•)Qualquer   │ │                                                      │ │
│ ( )Bom ( )Médio│ │                                  [📍 Minha localização]│ │
│ ( )Ruim       │ │                                                      │ │
│ ...x6         │ └──────────────────────────────────────────────────────┘ │
│[Aplicar][Limpar]│ Legenda: ✔ Bom  ◐ Médio  ✖ Ruim  ○ Sem avaliação       │
└───────────────┴──────────────────────────────────────────────────────────┘

Mobile (< 768 px)
┌────────────────────────────┐
│ ☰  Acessibiliza      [Aa]  │
│ 🔍 Buscar...               │
│ [Filtros (2)] [Mapa|Lista] │
│ ┌────────────────────────┐ │
│ │         MAPA           │ │
│ │                        │ │
│ └────────────────────────┘ │
│ ▔▔▔ bottom sheet ▔▔▔▔▔▔▔▔▔ │
│ Farol Shopping      ✔ Bom  │
│ Av. Marcolino…  [Ver]      │
├────────────────────────────┤
│ Explorar Avaliar Painel Conta│
└────────────────────────────┘
```

### Elementos

| Elemento | Detalhes |
|---|---|
| Campo de busca | `<input type="search">` com `<label>` visível ("Buscar local"). Consulta `name`; um segundo campo, ou um seletor "Buscar por: Nome / Endereço", consulta `address`. *Debounce* de 400 ms; o texto também é enviado com Enter |
| Alternador **Mapa / Lista** | Dois botões com `aria-pressed`, ou um grupo de abas. **Lista é o padrão quando um leitor de tela está ativo ou quando o usuário marcou "Preferir lista" nas preferências.** Guardar a escolha em `localStorage` e na URL (`?view=lista`) |
| Filtros | Um `<fieldset>` por critério com `<legend>` = nome do critério e rádios: Qualquer / Bom / Médio / Ruim. Botões "Aplicar filtros" e "Limpar filtros". No mobile, em um `Sheet` com foco preso. O botão mostra a contagem: "Filtros (2 ativos)" |
| Chips de filtros ativos | Acima dos resultados: "Cadeira de rodas: Bom ✕". Cada chip é um botão "Remover filtro Cadeira de rodas: Bom" |
| Contador de resultados | "23 locais encontrados", em `aria-live="polite"`, atualizado após cada busca |
| **Mapa** | Pinos coloridos **e com ícone/forma diferente** por classificação geral (ver §3.3). Clique ou Enter no pino abre o **cartão-resumo**. Controles de zoom visíveis. Botão "Minha localização" (pede a geolocalização só ao ser clicado). Fora do mapa, texto informando que o mesmo conteúdo existe na visualização em lista |
| **Lista** | `<ul>` de cartões `<li><article>`: nome (link para T2, `<h3>`), endereço, badges dos 6 critérios (ícone + texto + nota, ou "sem dados"), número de avaliações e, se a localização estiver ativa, a distância. Paginação com "Carregar mais" (usa `view.next`) e anúncio "Mais 30 locais carregados" |
| Cartão-resumo (mapa) | Popover/InfoWindow no desktop; *bottom sheet* no mobile. Nome, endereço, classificação geral, 3 critérios principais, botões **Ver detalhes** e **Avaliar**. Fecha com Esc e devolve o foco ao pino |
| Legenda | Sempre visível; explica cor, ícone e texto |
| Estado vazio | "Nenhum local encontrado com esses filtros." + botão "Limpar filtros" + link "Não achou o local? Avalie-o agora" |
| Carregando | Skeleton na lista; no mapa, overlay com `role="status"` "Carregando locais…" |
| Erro | "Não foi possível carregar os locais." + "Tentar novamente" |

### Dados

- Query: `GET /establishments?page=N&name=&address=&criterion_average[..]=..&order[name]=asc`
- React Query key: `["establishments", filtros, página]`
- Estado dos filtros **na URL** (`useSearchParams`), para permitir compartilhar, usar o "voltar" do navegador e recarregar sem perder a busca.
- O mapa precisa de todos os pinos, mas a API pagina em 30 itens. Enquanto a lacuna L7 não for resolvida, busque as páginas em sequência até `view.next` acabar, com um limite de segurança (ex.: 10 páginas), e agrupe os pinos (*marker clustering*) em zoom baixo.

---

## T2: Detalhe do local (`/locais/[id]`)

**Objetivo:** mostrar tudo sobre a acessibilidade de um local. Atende RF05, RF09 e RF13.

```
┌──────────────────────────────────────────────────────────────┐
│ ‹ Voltar para resultados            Início › Locais › Farol  │
│ <h1> Farol Shopping                                           │
│ Av. Marcolino Martins Cabral, 2525 – Tubarão/SC               │
│ ☎ (48) 3632-0000   🌐 Site   🧭 Como chegar   🔗 Compartilhar │
│ [ Avaliar este local ]                                        │
├──────────────────────────────────────────────────────────────┤
│ <h2> Resumo de acessibilidade      baseado em 12 avaliações   │
│ ┌─────────────────────────┬─────────┬──────────┬───────────┐ │
│ │ Critério                │ Média   │ Situação │ Avaliações│ │
│ │ ♿ Cadeira de rodas      │ 8,2/10  │ ✔ Bom    │ 10        │ │
│ │ 🚻 Banheiro acessível    │ 4,5/10  │ ✖ Ruim   │ 6         │ │
│ │ 👣 Piso tátil            │ —       │ ○ Sem dados│ 0       │ │
│ │ ...                     │         │          │           │ │
│ └─────────────────────────┴─────────┴──────────┴───────────┘ │
│ (barra visual por critério, decorativa: aria-hidden)          │
├──────────────────────────────────────────────────────────────┤
│ <h2> Avaliações (12)            Ordenar: [Mais úteis ▾]       │
│ <article>                                                     │
│   <h3 class=sr-only> Avaliação 1 de 12                        │
│   ♿ 9  🚻 2  👣 0                                             │
│   "Rampa na entrada principal, mas banheiro adaptado fechado."│
│   Foi útil?  [👍 Sim (5)]  [👎 Não (1)]   Saldo: +4          │
│ </article>                                                    │
│ ▸ 1 avaliação oculta por ser marcada como pouco útil [Mostrar]│
├──────────────────────────────────────────────────────────────┤
│ <h2> Localização  (mapa pequeno + endereço em texto)          │
└──────────────────────────────────────────────────────────────┘
```

| Elemento | Detalhes |
|---|---|
| Cabeçalho | `<h1>` com o nome; endereço em `<address>`; telefone como `tel:`; site com `rel="noopener"` e aviso de nova aba ("abre em nova aba") quando usar `target="_blank"`; "Como chegar" abre o Google Maps com o `googlePlaceId` |
| Compartilhar | `navigator.share` quando disponível; senão, copia o link e anuncia "Link copiado" |
| Resumo | **Tabela semântica** (`<table>`, `<caption>`, `<th scope>`), fonte `evaluationsSummary`. Nota com vírgula decimal (`Intl.NumberFormat('pt-BR')`), situação com ícone **e** texto. Critérios sem dados aparecem como "Sem dados" (não os oculte: a ausência de dados também é informação) |
| Classificação geral | Média simples das médias dos critérios avaliados, **com o rótulo "Média geral (X de 6 critérios avaliados)"**. Nunca apresente como nota oficial |
| Lista de avaliações | `evaluations` do detalhe. Ordenação no cliente: "Mais úteis" (`netVotes` desc, padrão) ou "Mais recentes" (depende da lacuna L2: `createdAt` não é público) |
| Nota por critério na avaliação | Lista de definições (`<dl>`): `<dt>Cadeira de rodas</dt><dd>9 de 10</dd>` |
| Votos | Dois botões com `aria-pressed`; rótulo acessível "Marcar avaliação como útil" / "como não útil". Anônimo que clica em votar vai para o login, que devolve à mesma avaliação (âncora `#avaliacao-<id>`). **Atualização otimista**, desfeita em caso de erro. O voto atual do usuário vem de `GET /evaluation_votes?evaluation=<iri>&user=<iri>` (só com login) |
| Avaliações com saldo `≤ -3` | Recolhidas em `<details>`, com a explicação de que não entram no resumo |
| Tempo real | Assinar o tópico Mercure do local; ao receber evento, mostrar "Há novas avaliações. [Atualizar]" (não recarregar sozinho) |
| Fotos (após lacuna L1) | Galeria com miniaturas `contentUrlSm`, abrindo `contentUrlLg` em um diálogo; `alt` informado pelo autor |
| 404 | "Local não encontrado" + link para o mapa |

Dados: `GET /establishments/{id}`. Key `["establishment", id]`. Use `generateMetadata` para o `<title>`: "Farol Shopping – Acessibilidade | Acessibiliza".

---

## T3: Nova avaliação (`/avaliar`)

**Objetivo:** registrar uma avaliação em poucos passos, com qualquer tecnologia assistiva. Atende RF07, RF08 e RF10.

Assistente de **4 passos**, com indicador "Passo 2 de 4: Critérios" (`<ol>` com `aria-current="step"`). Cada passo é uma seção com `<h2>`; ao avançar, o **foco vai para o `<h2>` do novo passo** e o `document.title` é atualizado.

```
Passo 1 de 4 — Escolha o local
┌──────────────────────────────────────────────┐
│ Local *                                        │
│ [ Digite o nome ou endereço do local...     ]  │  ← combobox (Google Places Autocomplete)
│   ▸ Farol Shopping – Av. Marcolino Martins...  │
│   ▸ Farmácia São João – Rua Lauro Müller...    │
│ ou [📍 Usar locais perto de mim]               │
│ Selecionado: Farol Shopping (já tem 12 avaliações — ver) │
│                                  [Próximo →]   │
└──────────────────────────────────────────────┘

Passo 2 de 4 — Avalie os critérios
┌──────────────────────────────────────────────┐
│ Avalie só o que você observou. Os demais podem ficar como "Não sei". │
│ <fieldset> <legend> ♿ Acesso para cadeira de rodas  (?) │
│   ( ) Não sei / não se aplica                  │
│   0  1  2  3  4  5  6  7  8  9  10             │  ← rádios grandes (≥ 44 px)
│   Não existe ···· Parcial ···· Totalmente adequado │
│   Selecionado: 8 — Adequado com pequenos problemas │
│ </fieldset>  × 6 critérios                     │
│                       [← Voltar] [Próximo →]   │
└──────────────────────────────────────────────┘

Passo 3 de 4 — Comentário e fotos
┌──────────────────────────────────────────────┐
│ Comentário (opcional)                          │
│ [textarea ................................. ]  │
│ Dica: descreva o que ajuda ou atrapalha. 0/1000│
│ Fotos (opcional) — após lacuna L1              │
│ [+ Adicionar foto]  miniatura + "Descrição da foto *" │
│                       [← Voltar] [Revisar →]   │
└──────────────────────────────────────────────┘

Passo 4 de 4 — Revise e envie
┌──────────────────────────────────────────────┐
│ Local: Farol Shopping            [Editar]      │
│ Critérios: Cadeira de rodas 8 · Piso tátil 0  [Editar] │
│ Comentário: "..."                [Editar]      │
│ ☐ Declaro que esta avaliação reflete minha experiência │
│                       [← Voltar] [Enviar avaliação] │
└──────────────────────────────────────────────┘
```

| Regra / comportamento | Detalhes |
|---|---|
| Login obrigatório | Anônimo que abre `/avaliar` vê "Para avaliar, entre com sua conta" + botão Entrar (`callbackURL=/avaliar…`). **Não bloqueie a navegação**: a pessoa pode preencher tudo e fazer login só ao enviar, desde que o rascunho seja preservado (`sessionStorage`) |
| Passo 1: escolher o local | Combobox acessível (padrão ARIA 1.2 *combobox* com *listbox*) sobre o **Places Autocomplete (New)**, restrito a Brasil e com viés para Tubarão (`locationBias` em um círculo de ~15 km). O componente pronto do Google não é totalmente acessível, então implemente o combobox próprio chamando o serviço `AutocompleteSuggestion`. Ao selecionar, mostre se o local já existe no Acessibiliza (busca `GET /establishments?name=`) |
| Entrada vinda do detalhe | `/locais/[id]/avaliar` preenche o passo 1 com o `googlePlaceId` e começa no passo 2. Se o local não tem `googlePlaceId` (cadastro manual feito pelo admin), mostre um aviso: não é possível avaliá-lo pela API atual |
| Passo 2: critérios | 6 `<fieldset>` de rádios (0–10 + "Não sei"). Padrão: **"Não sei"** (nada pré-marcado com nota). Pelo menos 1 critério com nota; caso contrário, erro "Avalie pelo menos um critério". Botão (?) abre a explicação do critério (disclosure) |
| Passo 3: comentário | `<textarea>` com contador anunciado de forma discreta ("restam 120 caracteres", `aria-live="polite"` só abaixo de 100 restantes). Limite sugerido: 1000 caracteres (lacuna: não há limite no backend) |
| Passo 4: revisão | Resumo com links "Editar" que levam ao passo e focam o campo |
| Envio | Botão com estado "Enviando…" (`aria-disabled`, sem remover o foco). Em caso de sucesso, redirecionar para `/locais/[id]` do estabelecimento retornado (`establishment` na resposta), com o toast "Obrigado! Sua avaliação foi publicada." e o foco no `<h1>`. Invalidar `["establishments"]` e `["establishment", id]` |
| Erro 422 de moderação | Voltar ao passo 3, mostrar a mensagem do servidor junto ao campo e no resumo de erros, focar o resumo |
| Erro 400 de Place ID | Voltar ao passo 1 com a mensagem |
| Erro 401 | Salvar o rascunho e ir ao login; ao retornar, restaurar o passo 4 |
| Sair com dados preenchidos | `beforeunload` + diálogo "Descartar avaliação?" nas navegações internas |

---

## T4: Painel de dados (`/painel`)

**Objetivo:** permitir que a comunidade e o poder público entendam a situação do município. Atende RF11 e RF12.

```
┌──────────────────────────────────────────────────────────────┐
│ <h1> Panorama da acessibilidade em Tubarão                    │
│ Filtros: Bairro/Endereço [____]   Critério [Todos ▾]          │
├──────────────┬──────────────┬──────────────┬─────────────────┤
│ 128 locais   │ 412 avaliações│ 38% Bom em   │ Critério mais   │
│ avaliados    │               │ cadeira rodas│ crítico: Braille│
├──────────────┴──────────────┴──────────────┴─────────────────┤
│ <h2> Média por critério        [Ver como tabela]              │
│  Barras horizontais 0–10 (rótulos diretos com valor)          │
├──────────────────────────────────────────────────────────────┤
│ <h2> Distribuição Bom / Médio / Ruim por critério             │
│  Barras empilhadas 100% com padrões (hachura) além de cor     │
├──────────────────────────────────────────────────────────────┤
│ <h2> Locais que mais precisam de atenção                      │
│  Tabela ordenável: Local | Média geral | Pior critério | Nº   │
├──────────────────────────────────────────────────────────────┤
│ [⬇ Baixar dados (CSV)]   Dados atualizados em 25/09/2026 14:02 │
│ Nota metodológica: médias ignoram avaliações com saldo ≤ -3…  │
└──────────────────────────────────────────────────────────────┘
```

| Elemento | Detalhes |
|---|---|
| Fonte de dados | **Hoje:** agregação no cliente sobre todas as páginas de `GET /establishments` (usa `evaluationsSummary`). Funciona para centenas de locais; para mais, veja a lacuna L4 (`GET /stats`) |
| Indicadores (KPIs) | Total de locais avaliados; total de avaliações (soma dos `count` máximos por local, aproximado, ou `totalItems` de `/evaluations`); % de locais "Bom" por critério; critério com pior média |
| Gráficos | Cada gráfico tem: título (`<h2>`), resumo textual de uma frase acima ("Piso tátil tem a pior média: 3,1"), **tabela de dados equivalente** (alternador "Ver como tabela" ou `<details>`) e nenhuma informação só por cor. Preferir SVG com `role="img"` + `aria-labelledby` apontando para título e resumo |
| Ranking | `<table>` com cabeçalhos ordenáveis (`<button>` dentro de `<th>`, `aria-sort`) |
| Exportar CSV | Gerado no cliente: `nome;endereço;latitude;longitude;<critério>_media;<critério>_n…` (separador `;` e BOM UTF-8, para abrir direito no Excel pt-BR) |
| Nota metodológica | Explicar as faixas (Bom ≥ 7, Médio 5–7, Ruim < 5), que os dados são colaborativos e não auditados e a regra dos votos |

---

## T6: Acessibilidade (`/acessibilidade`)

1. **Declaração de acessibilidade:** norma-alvo (WCAG 2.2 AA / NBR 17225 / eMAG), situação atual de conformidade, limitações conhecidas (ex.: "o mapa do Google tem limitações com leitores de tela; use a visualização em lista"), data da última avaliação e canal para relatar barreiras.
2. **Atalhos de teclado** da aplicação (ver 05-acessibilidade, §5.4.2).
3. **Preferências** (mesmo conteúdo do painel "Aa" do cabeçalho; ver 05-acessibilidade, §5.3).

---

## T7: Login (`/login`)

Já existe: redireciona ao Keycloak. Ajustes:
- Callback padrão `/` (hoje é `/books`).
- Texto visível enquanto redireciona: "Redirecionando para a página de login…" com `role="status"`. Não usar apenas o spinner.
- O tema do Keycloak precisa ser acessível e estar em pt-BR (tema customizado: contraste, rótulos e idioma). Isso faz parte do escopo do frontend.

---

## T8: Minhas avaliações (`/minhas-avaliacoes`): bloqueada

Lista das avaliações do usuário, com links para os locais e o saldo de votos. **Depende da lacuna L3** (filtro por autor e exposição de `createdAt`). Não incluir no menu até ser resolvida.

---

## T9: Administração (`/admin`)

Construída com React Admin + `@api-platform/admin` (base em `components/admin/`). É preciso **substituir os recursos do demo (books/reviews) por**:

| Recurso | Operações disponíveis na API | Tela |
|---|---|---|
| `admin/establishments` | PATCH, DELETE (sem listagem: lacuna L8) | Editar nome, endereço, telefone, site; remover |
| `admin/evaluations` | PATCH, DELETE (sem listagem: lacuna L8) | Ocultar (PATCH `active=false`, após a lacuna L2) ou remover avaliações abusivas |
| `admin/users` | GET coleção (`?name=`, `itemsPerPage`), GET item | Consultar usuários |

Enquanto a lacuna L8 não é resolvida, a lista de moderação pode usar os endpoints públicos (`/evaluations`, `/establishments`), com as ações apontando para `/admin/...`. Traduzir o React Admin para pt-BR (`ra-language-portuguese` ou mensagens próprias no `i18nProvider.ts`).

---

## T10: Erros e estados globais

| Página | Conteúdo |
|---|---|
| 404 (`app/not-found.tsx`) | `<h1>Página não encontrada</h1>`, texto simples, links para Explorar e Sobre |
| Erro (`app/error.tsx`) | "Algo deu errado." + botão "Tentar novamente" (`reset()`) |
| Offline | Aviso persistente `role="status"`: "Você está sem conexão. Os dados podem estar desatualizados." |
| Sessão expirada | Toast + botão "Entrar novamente"; preservar a tela atual |

## 3.2 Estados que toda tela com dados deve ter

| Estado | Padrão |
|---|---|
| Carregando | Skeleton com `aria-busy="true"` no contêiner e texto `sr-only` "Carregando…" |
| Vazio | Mensagem + próxima ação sugerida |
| Erro | Mensagem clara + "Tentar novamente" |
| Parcial | Ex.: local sem avaliações ("Ainda não há avaliações. Seja a primeira pessoa a avaliar!") |
| Sucesso | Toast `role="status"` + foco levado a um ponto lógico |

## 3.3 Representação da classificação (usada em todas as telas)

A situação **nunca** é comunicada só por cor (WCAG 1.4.1). Use sempre **cor + ícone + texto**:

| Situação | Faixa | Ícone | Texto | Cor do texto/borda (contraste AA sobre branco) | Fundo |
|---|---|---|---|---|---|
| Bom | ≥ 7 | `CircleCheck` ✔ | "Bom" | `#166534` (verde 800) | `#DCFCE7` |
| Médio | 5 a < 7 | `CircleAlert` ◐ | "Médio" | `#854D0E` (amarelo 800) | `#FEF9C3` |
| Ruim | < 5 | `CircleX` ✖ | "Ruim" | `#991B1B` (vermelho 800) | `#FEE2E2` |
| Sem dados | — | `CircleDashed` ○ | "Sem dados" | `#374151` (cinza 700) | `#F3F4F6` |

Pinos do mapa: forma e glifo distintos por situação (ex.: ✔ / ! / ✕ / ?) além da cor, com `title`/rótulo acessível "Farol Shopping, situação geral: Bom".

Função única em `lib/rating.ts`:

```ts
export type Status = "bom" | "medio" | "ruim" | "sem-dados";
export const statusFromAverage = (avg?: number | null): Status =>
  avg == null ? "sem-dados" : avg >= 7 ? "bom" : avg >= 5 ? "medio" : "ruim";
```
