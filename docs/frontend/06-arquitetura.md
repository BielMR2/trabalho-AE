# 6. Arquitetura e padrões de implementação

Complementa `pwa/FRONTEND_ARCHITECTURE.md` (visão geral da stack) com as decisões específicas do Acessibiliza.

## 6.1 Estrutura de pastas proposta (`pwa/`)

```
app/
  layout.tsx                 # <html lang="pt-BR">, providers, Layout, script de preferências no <head>
  page.tsx                   # T1 Explorar
  not-found.tsx / error.tsx  # T10
  locais/[id]/page.tsx       # T2 Detalhe (Server Component busca os dados iniciais)
  locais/[id]/avaliar/page.tsx
  avaliar/page.tsx           # T3
  painel/page.tsx            # T4
  sobre/page.tsx             # T5
  acessibilidade/page.tsx    # T6
  login/page.tsx             # T7 (existe)
  admin/page.tsx             # T9 (existe)
  api/auth/...               # better-auth (existe)
components/
  common/     Header, Footer, Layout, SkipLink, RouteAnnouncer, Announcer, VLibras, Loading, Error, Pagination
  a11y/       PreferencesDialog, PreferencesProvider, VisuallyHidden
  establishment/ EstablishmentCard, EstablishmentList, SummaryTable, StatusBadge, CriterionIcon
  map/        MapView, EstablishmentMarker, MarkerCluster, MapSummaryCard, LocateMeButton
  filters/    FilterPanel, CriterionFilterFieldset, ActiveFilterChips, SearchField
  evaluation/ EvaluationList, EvaluationItem, VoteButtons, EvaluationWizard/{StepPlace, StepCriteria, StepComment, StepReview}, RatingRadioGroup, PlacesCombobox, PhotoUploader
  dashboard/  KpiCard, CriterionAverageChart, StatusDistributionChart, RankingTable, CsvExportButton
  admin/      (React Admin)
  ui/         (Shadcn/Base UI — existente)
hooks/        useAuth (existe), useEstablishments, useEstablishment, useCreateEvaluation, useVote, useMyVote, usePreferences, useAnnounce, useMercure
lib/          criteria.ts, rating.ts, geo.ts, hydra.ts, csv.ts, format.ts (Intl pt-BR), queryKeys.ts
types/        Establishment.ts (existe), Evaluation.ts, Vote.ts, Image.ts, api.d.ts (gerado)
utils/        dataAccess.ts (existe), mercure.ts (existe)
```

Remover o que sobrou do demo e não é usado (`types/Thumbnails.ts`, recursos book/review em `components/admin/`, `public/api-platform/*`).

## 6.2 Tipos

Complementar `types/Establishment.ts` (o ideal é gerar `types/api.d.ts` com `openapi-typescript` e derivar os tipos a partir dele):

```ts
export type CriterionValue =
  | "wheelchair_accessible" | "accessible_restroom" | "tactile_paving"
  | "braille_signage" | "sign_language" | "service_animal_allowed";

export interface EvaluationRating { criterion: CriterionValue; rating: number } // 0–10
export interface EvaluationVote { "@id": string; value: 1 | -1 }

export interface Evaluation {
  "@id": string;
  comment?: string;
  ratings: EvaluationRating[];
  votes?: EvaluationVote[];
  netVotes: number;
  establishment?: string | Pick<Establishment, "@id" | "name" | "address">;
}

export type EvaluationsSummary = Partial<Record<CriterionValue, { average: number; count: number }>>;

export interface CreateEvaluationInput {
  establishmentGooglePlaceId: string;
  comment?: string;
  ratings: EvaluationRating[];
}
```

`lib/criteria.ts` centraliza a ordem de exibição, os rótulos, as descrições e os ícones (ver a tabela em 02-api §2.3.4). **Não repita** a lista de critérios em componentes (hoje ela está duplicada dentro de `FilterSidebar.tsx`).

## 6.3 Dados com React Query

| Hook | Query key | Endpoint | Observações |
|---|---|---|---|
| `useEstablishments(filters)` | `["establishments", filters]` | `GET /establishments` | `useInfiniteQuery` com `view.next`; `staleTime` de 60 s |
| `useAllEstablishments(filters)` | `["establishments", "all", filters]` | idem, todas as páginas | Mapa e painel; limite de páginas |
| `useEstablishment(id)` | `["establishment", id]` | `GET /establishments/{id}` | `initialData` vindo do Server Component |
| `useCreateEvaluation()` | mutation | `POST /evaluations` | `onSuccess`: invalida `["establishments"]` e `["establishment", id]` |
| `useVote()` | mutation | `POST /evaluation_votes` | Otimista (`onMutate` / `onError` com rollback) |
| `useMyVote(evaluationIri)` | `["my-vote", iri, userIri]` | `GET /evaluation_votes?evaluation=&user=` | Só com login; para muitas avaliações, prefira uma chamada por `evaluation` em lote |

Regras:
- Todas as chamadas passam por `fetchApi` (cabeçalhos, token, erros). Um helper `getMembers(data)` em `lib/hydra.ts` trata `member` / `hydra:member`.
- As query keys ficam em `lib/queryKeys.ts`.
- **Filtros na URL** (`useSearchParams` + `router.replace`), com serialização curta: `?q=&end=&cr=bom&bn=ruim&view=lista`.
- Mercure: `useMercure(topic, onEvent)` sobre `utils/mercure.ts`, invalidando as queries (sem sobrescrever o cache diretamente).

## 6.4 Formulários

- `react-hook-form` + `yup` (já instalados). Schema da avaliação:

```ts
const schema = yup.object({
  placeId: yup.string().required("Escolha o local que você quer avaliar."),
  ratings: yup.object().test("ao-menos-um", "Avalie pelo menos um critério.",
    (r) => Object.values(r ?? {}).some((v) => v !== "nao-sei")),
  comment: yup.string().max(1000, "O comentário pode ter no máximo 1000 caracteres."),
});
```

- O rascunho é salvo em `sessionStorage` (`acessibiliza:rascunho-avaliacao`) a cada mudança de passo e limpo após o sucesso.
- Os erros do servidor (`FetchError.fields`) entram no formulário com `setError`, pelo mapeamento de `propertyPath` descrito em 02-api §2.4.

## 6.5 Design system

- Tokens em CSS (`styles/globals.css`, Tailwind 4 `@theme`), com variantes para `[data-contrast="alto"]` e `[data-theme="dark"]`:

```css
@theme {
  --color-primary: #155E75;       /* cyan-800: 7:1 sobre branco */
  --color-primary-hover: #164E63;
  --color-focus: #F59E0B;         /* anel de foco, com contorno escuro */
  --color-status-bom: #166534;    --color-status-bom-bg: #DCFCE7;
  --color-status-medio: #854D0E;  --color-status-medio-bg: #FEF9C3;
  --color-status-ruim: #991B1B;   --color-status-ruim-bg: #FEE2E2;
  --color-status-nd: #374151;     --color-status-nd-bg: #F3F4F6;
}
```

- Componentes base de `components/ui/` (Shadcn/Base UI) para Dialog, Sheet, Drawer, Toast e Button. **Todo componente novo** deve ter: nome acessível, estados de foco, hover, ativo e desabilitado, e alvo ≥ 44 px.
- Formatação: `Intl.NumberFormat("pt-BR", { maximumFractionDigits: 1 })` para as médias ("8,2"); `Intl.DateTimeFormat("pt-BR")` para as datas.
- *Breakpoints* Tailwind: `sm` 640, `md` 768 (sidebar de filtros aparece), `lg` 1024.

## 6.6 Variáveis de ambiente

| Variável | Uso |
|---|---|
| `NEXT_PUBLIC_ENTRYPOINT` | URL da API no servidor |
| `NEXT_PUBLIC_GOOGLE_MAPS_API_KEY` | Maps + Places (restringir por domínio) |
| `NEXT_PUBLIC_OIDC_CLIENT_ID`, `NEXT_PUBLIC_OIDC_SERVER_URL`, `NEXT_PUBLIC_OIDC_SERVER_URL_INTERNAL`, `OIDC_CLIENT_SECRET` | Keycloak |
| `BETTER_AUTH_SECRET`, `BETTER_AUTH_DATABASE_URL` | Sessão do `better-auth` |
| `NEXT_PUBLIC_MAP_DEFAULT_CENTER` (novo, opcional) | `-28.4667,-49.0069` |

## 6.7 Desempenho

- Detalhe do local renderizado no servidor (SEO e compartilhamento com Open Graph: nome + resumo).
- Google Maps carregado sob demanda (a lista aparece antes do mapa).
- *Marker clustering* acima de ~50 pinos.
- Imagens via `next/image` com as variantes do LiipImagine.
- Metas: LCP < 2,5 s, INP < 200 ms, CLS < 0,1.

## 6.8 Testes

| Camada | Ferramenta | O que testar |
|---|---|---|
| Lint | ESLint 9 + `eslint-plugin-jsx-a11y` | Regras de acessibilidade estáticas |
| E2E | Playwright (`e2e/tests/`) + mock server (`e2e/mock-server/`) | F1–F5, incluindo os erros 401/422 (mockar a moderação), navegação só por teclado e `@axe-core/playwright` |
| Unitário (opcional) | Vitest + Testing Library | `statusFromAverage`, `parsePoint`, montagem do corpo do POST, geração de CSV |

Cenários E2E mínimos:
1. Anônimo filtra por "Cadeira de rodas: Bom" e o contador é anunciado.
2. Alternar para a lista e abrir um detalhe pelo teclado; o foco vai para o `<h1>`.
3. Anônimo inicia uma avaliação, faz login e volta ao passo 4 com o rascunho.
4. Envio com comentário reprovado (mock 422) mostra o erro no campo e no resumo.
5. Voto otimista com rollback em caso de erro.
6. axe sem violações em todas as rotas públicas, com e sem alto contraste.

## 6.9 Convenções

- Commits e títulos de PR em **Conventional Commits** (`feat(pwa): …`).
- Componentes funcionais; sem `console.log` no código versionado.
- Textos da interface em pt-BR, centralizados em `lib/i18n/pt-BR.ts` para facilitar a revisão de linguagem simples.
