# 5. Acessibilidade

> Um aplicativo que mapeia barreiras de acessibilidade não pode criar barreiras digitais. Os itens desta seção são **critérios de aceite**: uma tela só está pronta quando cumpre o checklist em §5.7.

## 5.1 Normas e referências

| Referência | Aplicação |
|---|---|
| **Lei 13.146/2015 (LBI), art. 63** | Obrigatoriedade de acessibilidade em sítios da internet |
| **Decreto 6.949/2009** | Desenho universal e adaptação razoável |
| **WCAG 2.2, nível AA** (W3C) | Meta técnica principal |
| **ABNT NBR 17225:2025** | Norma brasileira de acessibilidade em conteúdo e aplicações web, alinhada às WCAG |
| **eMAG 3.1** | Modelo de Acessibilidade em Governo Eletrônico: recomendações adotadas no Brasil (atalhos, barra de acessibilidade) |
| **WAI-ARIA 1.2 / APG** | Padrões de componentes (combobox, dialog, tabs, radio group) |

## 5.2 Princípios de projeto

1. **Alternativa ao mapa:** todo conteúdo e toda ação do mapa existem também na **lista**. O mapa é um complemento visual, nunca o único caminho.
2. **Nada só por cor:** a situação usa sempre cor, ícone e texto.
3. **Teclado primeiro:** tudo funciona sem mouse, com ordem de foco lógica e foco sempre visível.
4. **HTML semântico antes de ARIA:** `<button>`, `<a>`, `<fieldset>`, `<table>`, landmarks. ARIA só quando não houver elemento nativo.
5. **Linguagem simples:** frases curtas, verbos no imperativo nos botões ("Avaliar local"), sem jargão. Isso também ajuda pessoas surdas, que têm o português como segunda língua.
6. **Tolerância a erros:** confirmação antes de ações destrutivas, rascunho preservado, mensagens que dizem **como corrigir**.
7. **Respeito às preferências do sistema:** `prefers-reduced-motion`, `prefers-contrast`, `prefers-color-scheme`, zoom e tamanho de fonte do navegador.

## 5.3 Recursos de acessibilidade do aplicativo

### 5.3.1 Painel de preferências ("Aa")

Acessível pelo cabeçalho em todas as páginas e pelo atalho `Alt+Shift+A`. As opções são salvas em `localStorage` e aplicadas em `<html>`:

| Preferência | Implementação | Critério WCAG |
|---|---|---|
| Tamanho do texto: 100% / 125% / 150% / 200% | `html { font-size: var(--base) }`; tudo em `rem` | 1.4.4 |
| Alto contraste | `data-contrast="alto"` troca os tokens de cor (fundo preto, texto branco/amarelo, contraste ≥ 7:1) | 1.4.6 (AAA, bônus) |
| Tema escuro | `data-theme="dark"` ou seguir o sistema | — |
| Reduzir animações | `data-motion="reduzido"` + `@media (prefers-reduced-motion)` desativam transições, animação de pinos e *smooth scroll* | 2.3.3 |
| Fonte para dislexia | Troca a família (ex.: Atkinson Hyperlegible ou OpenDyslexic) | — |
| Espaçamento de texto ampliado | `line-height: 1.8; letter-spacing: .12em; word-spacing: .16em` | 1.4.12 |
| Sublinhar todos os links | `a { text-decoration: underline }` | 1.4.1 |
| Preferir lista ao mapa | Define a visualização padrão de T1 | — |
| Restaurar padrão | Limpa tudo | — |

### 5.3.2 Libras: VLibras

Integrar o widget oficial **VLibras** (governo federal), que traduz o conteúdo textual para Libras com um avatar:

```tsx
// app/layout.tsx (Client Component separado: components/common/VLibras.tsx)
<div vw="" className="enabled">
  <div vw-access-button="" className="active" />
  <div vw-plugin-wrapper=""><div className="vw-plugin-top-wrapper" /></div>
</div>
<Script src="https://vlibras.gov.br/app/vlibras-plugin.js" strategy="lazyOnload"
  onLoad={() => new (window as any).VLibras.Widget("https://vlibras.gov.br/app")} />
```

- Verifique que o botão do VLibras não cobre o botão "Minha localização" nem a barra inferior no mobile.
- Na página Sobre, inclua vídeo em Libras gravado por intérprete, quando possível.

### 5.3.3 Atalhos de teclado (padrão eMAG)

| Atalho | Ação |
|---|---|
| `Alt+1` | Ir para o conteúdo principal |
| `Alt+2` | Ir para o menu principal |
| `Alt+3` | Ir para a busca |
| `Alt+4` | Ir para o rodapé |
| `Alt+Shift+A` | Abrir preferências de acessibilidade |
| `/` | Focar a busca (fora de campos de texto) |
| `Esc` | Fechar diálogo, painel ou cartão-resumo |

Os atalhos ficam listados em `/acessibilidade`. Não use atalhos de uma única tecla com letras (WCAG 2.1.4), a não ser que o usuário possa desativá-los. `/` é a exceção: fica ativo só fora de campos e pode ser desligado nas preferências.

## 5.4 Padrões por componente

### 5.4.1 Estrutura da página
- `<html lang="pt-BR">`. Trechos em outro idioma com `lang` próprio.
- Landmarks: `<header>`, `<nav aria-label="Principal">`, `<main id="conteudo">`, `<aside aria-label="Filtros">`, `<footer>`.
- Um `<h1>` por página, com hierarquia sem saltos.
- `<title>` único por página: "Página – Acessibiliza".
- **Troca de rota (SPA):** ao navegar, mover o foco para o `<h1>` (ou para `<main>` com `tabIndex={-1}`) e anunciar o título. Faça isso em um componente `RouteAnnouncer` que observa `usePathname()`.

### 5.4.2 Foco e teclado
- Foco visível em todos os elementos interativos: `outline: 3px solid var(--focus); outline-offset: 2px` com contraste ≥ 3:1 (WCAG 2.4.7 e 2.4.11). **Nunca** `outline: none` sem substituto.
- O foco não fica escondido sob o cabeçalho fixo nem sob o *bottom sheet* (2.4.11): use `scroll-padding-top`.
- A ordem de foco segue a ordem visual. Não use `tabindex` > 0.
- Diálogos e *sheets*: foco preso, `Esc` fecha e o foco volta ao elemento que abriu. Os componentes Base UI de `components/ui/` já fazem isso; verifique em cada uso.

### 5.4.3 Mapa (Google Maps)

O Google Maps tem limitações com leitores de tela, então:
- Contêiner do mapa com `role="region"` e `aria-label="Mapa de locais avaliados"`, seguido de um texto `sr-only`: "O mapa mostra 23 locais. A mesma informação está na visualização em lista." + link para a lista.
- **Pinos** (`AdvancedMarker`): precisam ser focáveis e ter nome acessível. Passe `title` com "Nome, situação geral: Bom". Confira no navegador se o marcador fica focável; se não ficar, renderize um `<button>` próprio dentro do `AdvancedMarker`, com `aria-label`.
- Enter/Espaço no pino abre o cartão-resumo. `Esc` fecha e devolve o foco ao pino.
- Setas movem o mapa (comportamento nativo) e `+`/`-` controlam o zoom. Mantenha os controles de zoom visíveis (`zoomControl`).
- `gestureHandling`: `"greedy"` (atual) atrapalha a rolagem da página no celular quando o mapa não ocupa a tela toda. Use `"cooperative"` quando o mapa estiver dentro de uma página rolável (ex.: detalhe) e `"greedy"` só em tela cheia.
- Agrupamentos (*clusters*): nome acessível "12 locais nesta área. Ampliar".
- Não esconder os pontos de interesse (POIs) do Google (hoje `MAP_STYLES` oculta `poi` e `transit`): eles ajudam na orientação. Avalie com usuários.

### 5.4.4 Busca e combobox (Places)
- Padrão APG **Combobox com listbox**: `role="combobox"`, `aria-expanded`, `aria-controls`, `aria-activedescendant`, opções com `role="option"`.
- Anunciar "5 sugestões disponíveis. Use as setas para navegar."
- Exibir "Powered by Google" (exigência de licença) com texto acessível.

### 5.4.5 Filtros
- Um `<fieldset>` por critério, com `<legend>`. As opções são **rádios** (Qualquer/Bom/Médio/Ruim), e não botões com cor, como hoje em `FilterSidebar.tsx`.
- Todo `<label>` ligado ao campo por `htmlFor`/`id` (hoje os rótulos "Nome" e "Endereço" não estão associados).
- Aplicar filtros não move o foco; o resultado é anunciado pela região *live*.

### 5.4.6 Formulário de avaliação
- Campos obrigatórios com texto "(obrigatório)" no rótulo, e não só `*`; `aria-required="true"`.
- Instruções antes do campo (`aria-describedby`).
- **Nota 0–10:** grupo de rádios nativos estilizados, dentro de `<fieldset>`. Alvo mínimo de **44×44 px** (acima do mínimo de 24 px do critério 2.5.8), com o texto da âncora ("8: Adequado, com pequenos problemas") exibido e associado. Não use *slider*: é difícil com tremor e com leitor de tela.
- Validação: no envio e ao sair do campo, **não a cada tecla**. Erros com `aria-invalid="true"`, mensagem ligada por `aria-describedby`, ícone + texto em vermelho acessível e **resumo de erros** no topo (`role="alert"`, com links para cada campo).
- Contador de caracteres com anúncio moderado (ver T3).
- Sem limite de tempo para preencher (2.2.1). Rascunho preservado.
- Botão desabilitado: prefira `aria-disabled` com explicação a `disabled` (que tira o botão da ordem de foco e esconde o motivo).

### 5.4.7 Imagens e ícones
- Ícones decorativos: `aria-hidden="true"`. Ícones que funcionam como botão precisam de nome acessível (`aria-label` ou texto `sr-only`).
- **Fotos enviadas pelos usuários:** o campo "Descrição da foto" é **obrigatório** no upload (vira o `alt`), com dica: "Descreva o que a foto mostra sobre a acessibilidade. Ex.: 'Escada de 3 degraus sem rampa na entrada'."
- Use `next/image` com as variantes `contentUrlXs/Sm/Md/Lg` via `sizes`.

### 5.4.8 Tabelas e gráficos
- Tabelas com `<caption>`, `<th scope="col|row">`; tabelas largas com rolagem horizontal **no contêiner** (`tabIndex=0`, `role="region"`, `aria-label`).
- Gráficos: resumo textual + tabela equivalente, padrões (hachuras) além de cor, contraste ≥ 3:1 entre as séries e o fundo (1.4.11), rótulos de valor diretos.

### 5.4.9 Mensagens dinâmicas
- Um único **anunciador global** (`AnnouncerProvider` com `announce(msg, "polite"|"assertive")`). As regiões *live* precisam existir no DOM **antes** de receber texto.
- `polite`: contagem de resultados, voto registrado, preferência aplicada, "carregando".
- `assertive` / `role="alert"`: erros de envio, sessão expirada.
- Toasts ficam visíveis por pelo menos 6 s, **pausam com hover/foco** e podem ser fechados; ações importantes nunca aparecem só em toast.

### 5.4.10 Links e botões
- Links para navegação e botões para ações. **Nada de `<a href="#">` com `onClick`**, como no `Header.tsx` atual (entrar/sair). Também não use `role="menuitem"` fora de um `role="menu"`.
- Textos únicos e descritivos: em vez de vários "Ver", use "Ver detalhes de Farol Shopping" (texto `sr-only` complementar).
- Links externos: indicar "(abre em nova aba)".

## 5.5 Design visual acessível

| Token | Regra |
|---|---|
| Contraste de texto | ≥ 4,5:1 (texto normal) e ≥ 3:1 (≥ 18,66 px negrito / 24 px) |
| Contraste de componentes e bordas de campos | ≥ 3:1 (1.4.11). Hoje `border-gray-200` nos filtros **não atinge**; use no mínimo `gray-500` |
| Cor primária | O ciano atual (`#0f929a` no pino) tem contraste insuficiente para texto branco em tamanho normal (~3,8:1). Use `cyan-800` (`#155E75`, ~7:1) em botões com texto branco. Confira todas as combinações com o WebAIM Contrast Checker |
| Tipografia | Mínimo de 16 px no corpo; `line-height` ≥ 1.5; até ~80 caracteres por linha; Poppins (já instalada) ou Atkinson Hyperlegible |
| Alvos de toque | ≥ 44×44 px nos controles principais; ≥ 24×24 px em qualquer caso (2.5.8) |
| Zoom | Layout íntegro em 400% / 320 px de largura (1.4.10). **Nunca** `maximum-scale=1` nem `user-scalable=no` |
| Movimento | Sem animações automáticas acima de 5 s; nada que pisque mais de 3 vezes por segundo |
| Orientação | Retrato e paisagem (1.3.4) |

## 5.6 Pontos do código atual (`pwa/`) a corrigir

| Arquivo | Problema | Correção |
|---|---|---|
| `components/common/Header.tsx` | `<a href="#">` + `onClick` + `role="menuitem"`; textos "Sign out"/"Login" em inglês | `<button>`; "Entrar" / "Sair"; remover `role` |
| `components/home/FilterSidebar.tsx` | `<label>` sem `htmlFor`; opções como botões sem `aria-pressed`; estado só por cor; bordas com pouco contraste; `title` como única explicação | `fieldset`/`legend` + rádios; `htmlFor`; ícone + texto; bordas `gray-500` |
| `components/home/MapView.tsx` | Centro em São Paulo; sem alternativa em lista; geolocalização pedida no carregamento; pinos iguais para qualquer situação; `gestureHandling="greedy"` | Centro em Tubarão; lista; geolocalização só por ação do usuário; pinos por situação; texto alternativo da região |
| `components/home/EstablishmentDrawer.tsx` | Arquivo vazio (quebra o import em `app/page.tsx`) | Implementar o cartão-resumo (T1) |
| `app/login/page.tsx` | Callback padrão `/books`; só spinner | `/`; texto com `role="status"` |
| `app/layout.tsx` / `components/common/Layout.tsx` | `<html lang="en">`; título "Welcome to API Platform!"; sem skip link, `<main>` e rodapé | `lang="pt-BR"`; título "Acessibiliza"; skip link + landmarks (§5.4.1) |
| `components/admin/*` | Recursos do demo (books/reviews), i18n francês/inglês | Recursos do Acessibiliza, pt-BR |

## 5.7 Checklist de aceite (por tela/PR)

**Teclado e foco**
- [ ] Todas as ações funcionam só com teclado (Tab, Shift+Tab, Enter, Espaço, setas, Esc)
- [ ] Foco sempre visível e nunca escondido
- [ ] Ordem de foco lógica; o foco vai para o `<h1>` na troca de rota e volta ao gatilho ao fechar diálogos

**Leitor de tela**
- [ ] Landmarks e títulos corretos; um `<h1>`
- [ ] Todo controle tem nome, papel e estado (`aria-pressed`, `aria-expanded`, `aria-invalid`…)
- [ ] Mudanças dinâmicas anunciadas (resultados, erros, sucesso)
- [ ] Imagens com `alt` adequado; ícones decorativos ocultos

**Visual**
- [ ] Contraste AA (texto 4,5:1; componentes 3:1)
- [ ] Nenhuma informação só por cor
- [ ] Funciona com zoom de 200% e 400% e a 320 px de largura, sem rolagem horizontal
- [ ] Funciona com as preferências de alto contraste, fonte 200% e movimento reduzido

**Formulários**
- [ ] Rótulos visíveis e associados; obrigatoriedade em texto
- [ ] Erros junto ao campo + resumo; mensagens dizem como corrigir
- [ ] Rascunho preservado em caso de erro, login ou saída acidental

**Conteúdo**
- [ ] Português claro; siglas explicadas na primeira ocorrência
- [ ] `<title>` único e descritivo

## 5.8 Como testar

| Tipo | Ferramenta | Quando |
|---|---|---|
| Automático em componentes | `eslint-plugin-jsx-a11y` (adicionar ao `eslint.config.mjs`) | A cada commit (`pnpm lint`) |
| Automático E2E | `@axe-core/playwright` nos testes de `e2e/tests/`: `expect(violations).toEqual([])` em cada rota, também com o painel de preferências ativo | CI |
| Auditoria | Lighthouse (Acessibilidade = 100), WAVE, Accessibility Insights | A cada release |
| Manual: leitores de tela | NVDA + Firefox, VoiceOver + Safari (iOS/macOS), TalkBack + Chrome (Android) | A cada release, nos fluxos F1–F4 |
| Manual: teclado | Percorrer F1–F4 sem mouse | A cada PR com UI |
| Manual: zoom e *reflow* | 200% / 400%, 320 px | A cada PR com UI |
| **Teste com usuários** | Sessões com pessoas das personas (associações locais de PcD de Tubarão, NAPNE do IFSC) | A cada marco do projeto |

Exemplo de teste E2E:

```ts
import { test, expect } from "@playwright/test";
import AxeBuilder from "@axe-core/playwright";

for (const path of ["/", "/painel", "/sobre", "/acessibilidade"]) {
  test(`sem violações de acessibilidade em ${path}`, async ({ page }) => {
    await page.goto(path);
    const results = await new AxeBuilder({ page })
      .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa", "wcag22aa"])
      .exclude(".gm-style") // canvas do Google Maps: avaliado manualmente
      .analyze();
    expect(results.violations).toEqual([]);
  });
}
```
