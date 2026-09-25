# 2. Contrato da API

Fonte: entidades em `api/src/Entity/`, processadores em `api/src/State/Processor/`, filtros em `api/src/Filter/` e configuração em `api/config/`.

> **Fonte única da verdade:** a especificação OpenAPI gerada pelo API Platform (`/docs`, `/docs.jsonopenapi`). Se o backend mudar, regenere os tipos:
> ```bash
> cd pwa && npx openapi-typescript ../api/public/docs.jsonopenapi -o types/api.d.ts
> ```
> Os exemplos JSON abaixo mostram o formato esperado. Confirme os detalhes (por exemplo, se um campo aninhado vem como objeto ou como IRI) no `/docs` antes de implementar.

## 2.1 Convenções gerais

| Item | Valor |
|---|---|
| Base URL | `ENTRYPOINT` (`pwa/config/entrypoint.ts`): mesma origem no navegador e `NEXT_PUBLIC_ENTRYPOINT` no servidor |
| Formato padrão | JSON-LD/Hydra: `Accept: application/ld+json` |
| Outros formatos | `application/json`, `multipart/form-data` (upload) |
| PATCH | `Content-Type: application/merge-patch+json` |
| Identificadores | UUID; os recursos são referenciados por **IRI** (`/establishments/0190…`) |
| Paginação | `?page=N`, 30 itens por página (padrão do API Platform). Resposta com `totalItems` e `view.next` / `view.last` |
| Campos nulos | omitidos (`SKIP_NULL_VALUES`). Trate todo campo opcional como possivelmente **ausente** |
| Erros | RFC 7807 (`title`, `detail`, `status`, `violations[]`) |
| Cliente HTTP | `fetchApi()` em `pwa/utils/dataAccess.ts`, que já define os cabeçalhos, o token e a conversão de violações em `FetchError.fields` |

Formato de coleção:

```json
{
  "@context": "/contexts/Establishment",
  "@id": "/establishments",
  "@type": "Collection",
  "totalItems": 42,
  "member": [ /* itens */ ],
  "view": { "@id": "/establishments?page=1", "first": "...", "last": "...?page=2", "next": "...?page=2" }
}
```

> Versões antigas do Hydra usam o prefixo `hydra:` (`hydra:member`). O código atual em `pwa/app/page.tsx` já trata os dois casos. Mantenha esse cuidado em um helper único.

## 2.2 Autenticação e autorização

- **Provedor:** Keycloak (OIDC). No frontend, `better-auth` com o plugin `genericOAuth` (`pwa/lib/auth.ts`), fluxo Authorization Code + PKCE.
- **Token:** o hook `useAccessToken()` (`pwa/hooks/useAuth.ts`) devolve o access token, que vai no cabeçalho `Authorization: Bearer <token>` (o `fetchApi` faz isso quando recebe o token).
- **Papéis:** o backend lê `realm_access.roles` do JWT. `is_granted("OIDC_USER")` exige o papel `user` e `is_granted("OIDC_ADMIN")` exige o papel `admin`.
- **Entrar:** `signInWithKeycloak(callbackURL)`. **Sair:** `signOutWithKeycloak(redirectUri)`.

| Ação | Anônimo | `user` | `admin` |
|---|:-:|:-:|:-:|
| Ver e filtrar estabelecimentos e avaliações | ✅ | ✅ | ✅ |
| Criar avaliação | ❌ 401 | ✅ | ✅ |
| Votar em avaliação e listar votos | ❌ 401 | ✅ | ✅ |
| Enviar imagem ou arquivo | ✅ (sem regra de segurança; ver lacuna L1) | ✅ | ✅ |
| Editar ou remover estabelecimento ou avaliação (`/admin/*`) | ❌ | ❌ 403 | ✅ |
| Listar usuários (`/admin/users`) | ❌ | ❌ 403 | ✅ |

## 2.3 Recursos

### 2.3.1 Estabelecimento (`Establishment`, schema.org `LocalBusiness`)

| Método | Rota | Acesso | Uso no frontend |
|---|---|---|---|
| GET | `/establishments` | público | Mapa, lista, painel |
| GET | `/establishments/{id}` | público | Página de detalhe (inclui as avaliações completas) |
| PATCH | `/admin/establishments/{id}` | admin | Corrigir dados |
| DELETE | `/admin/establishments/{id}` | admin | Remover (antes, o backend desativa as avaliações do local) |

> Não existe `POST /establishments`: o estabelecimento é criado **automaticamente** pela primeira avaliação (ver 2.3.2).

Campos:

| Campo | Tipo | Observação |
|---|---|---|
| `@id` | IRI | Use como chave (React `key`, cache) |
| `googlePlaceId` | string? | ID do Google Places; único |
| `name` | string | Ex.: "Farol Shopping" |
| `address` | string? | Endereço formatado (Google, pt-BR) |
| `phoneNumber` | string? | Formato nacional |
| `website` | string? | URL |
| `location` | string | Geometria WKT: `"POINT(<longitude> <latitude>)"`. **Atenção à ordem: longitude primeiro** |
| `evaluations` | IRI[] na coleção / objetos no detalhe | No `GET /establishments/{id}` vêm completas (grupo `Evaluation:read`) |
| `evaluationsSummary` | objeto | Média e contagem por critério (ver abaixo) |

`evaluationsSummary`: calculado no servidor. **Ignora avaliações com saldo de votos `≤ -3`**, e as médias têm duas casas decimais. Só aparecem os critérios que receberam alguma nota:

```json
"evaluationsSummary": {
  "wheelchair_accessible": { "average": 7.5, "count": 4 },
  "accessible_restroom":  { "average": 3.0, "count": 2 }
}
```

Exemplo de `GET /establishments/{id}`:

```json
{
  "@context": "/contexts/Establishment",
  "@id": "/establishments/01923f6e-0000-7000-8000-000000000001",
  "@type": "https://schema.org/LocalBusiness",
  "googlePlaceId": "ChIJ0Qh_Ld89Xo4RJj6lF8sS9aA",
  "name": "Farol Shopping",
  "address": "Av. Marcolino Martins Cabral, 2525 - Vila Moema, Tubarão - SC",
  "phoneNumber": "(48) 3632-0000",
  "website": "https://www.farolshopping.com.br/",
  "location": "POINT(-49.0069 -28.4667)",
  "evaluations": [
    {
      "@id": "/evaluations/01923f6e-0000-7000-8000-0000000000aa",
      "@type": "https://schema.org/Review",
      "comment": "Rampa na entrada principal, mas banheiro adaptado fechado.",
      "ratings": [
        { "criterion": "wheelchair_accessible", "rating": 8 },
        { "criterion": "accessible_restroom", "rating": 2 }
      ],
      "votes": [ { "@id": "/evaluation_votes/…", "value": 1 } ],
      "netVotes": 1
    }
  ],
  "evaluationsSummary": {
    "wheelchair_accessible": { "average": 8, "count": 1 },
    "accessible_restroom": { "average": 2, "count": 1 }
  }
}
```

Conversão de `location` (já implementada em `MapView.tsx`; mova-a para `lib/geo.ts`):

```ts
export function parsePoint(wkt?: string | null): { lat: number; lng: number } | null {
  const m = wkt?.match(/POINT\s*\(\s*([-\d.]+)\s+([-\d.]+)\s*\)/i);
  return m ? { lng: parseFloat(m[1]), lat: parseFloat(m[2]) } : null;
}
```

#### Filtros de `GET /establishments`

| Parâmetro | Tipo | Comportamento | Exemplo |
|---|---|---|---|
| `name` | string | Busca parcial | `?name=farmácia` |
| `address` | string | Busca parcial | `?address=centro` |
| `order[name]` | `asc`/`desc` | Ordenação | `?order[name]=asc` |
| `criterion_average[<criterio>]` | `bom`/`medio`/`ruim` | Média do critério na faixa. Vários critérios são combinados com **E** | `?criterion_average[wheelchair_accessible]=bom&criterion_average[tactile_paving]=ruim` |
| `page` | int | Paginação | `?page=2` |

Faixas do filtro (`CriterionAverageFilter`):

| Valor | Faixa | Rótulo na UI |
|---|---|---|
| `ruim` | média `< 5` | Ruim |
| `medio` | `5 ≤ média < 7` | Médio |
| `bom` | média `≥ 7` | Bom |

> ⚠️ O filtro calcula a média com **todas** as avaliações, enquanto `evaluationsSummary` exclui as que têm saldo `≤ -3`. Nos casos-limite, um local pode aparecer no filtro "Bom" e exibir média "Médio" no detalhe. Veja a lacuna L6. Na UI, **use sempre `evaluationsSummary`** para exibir a classificação.

### 2.3.2 Avaliação (`Evaluation`, schema.org `Review`)

| Método | Rota | Acesso | Uso |
|---|---|---|---|
| GET | `/evaluations` | público | Feed de avaliações recentes, painel |
| GET | `/evaluations/{id}` | público | Link direto para uma avaliação |
| POST | `/evaluations` | `user` | Criar avaliação |
| PATCH | `/admin/evaluations/{id}` | admin | Moderação |
| DELETE | `/admin/evaluations/{id}` | admin | Moderação |

Campos de leitura: `@id`, `comment?`, `ratings[]` (`criterion`, `rating`), `votes[]` (`@id`, `value`), `establishment` (dados básicos do local), `netVotes` (soma dos votos).

**Criar (`POST /evaluations`):**

```json
{
  "establishmentGooglePlaceId": "ChIJ0Qh_Ld89Xo4RJj6lF8sS9aA",
  "comment": "Entrada com rampa e corrimão. Não há piso tátil.",
  "ratings": [
    { "criterion": "wheelchair_accessible", "rating": 9 },
    { "criterion": "tactile_paving", "rating": 0 }
  ]
}
```

Regras (validadas no servidor):

| Regra | Origem | Resposta em caso de erro |
|---|---|---|
| `establishmentGooglePlaceId` obrigatório | `EvaluationPersistProcessor` | **400** `establishmentGooglePlaceId is required` |
| Place ID precisa existir no Google | `GooglePlacesClient` | **400** `Invalid Google Place ID or unable to fetch details: …` |
| `criterion` é um dos 6 valores do enum | `CriterionEnum` | **400/422** |
| `rating` inteiro entre **0 e 10** | `Assert\Range(0, 10)` | **422** `violations[].propertyPath = "ratings[0].rating"` |
| `comment` passa pela moderação da OpenAI | `OpenAiModeration` | **422** "O comentário viola nossas diretrizes de comunidade por conter linguagem inapropriada, assédio ou discurso de ódio." (`propertyPath: "comment"`) |
| Usuário autenticado | `security` | **401** |

Comportamento: se ainda não existe estabelecimento com esse `googlePlaceId`, o backend consulta o Google Places (nome, localização, endereço, telefone, site, em pt-BR) e **cria o estabelecimento** junto com a avaliação.

Observações para a UI:
- `ratings` pode conter **apenas os critérios que a pessoa sabe avaliar**. Ofereça "Não sei / não se aplica" em cada critério e **não envie** os critérios marcados assim.
- Mande pelo menos 1 critério. O backend não exige isso hoje, mas uma avaliação sem notas não contribui para o resumo; valide no cliente.
- A resposta **não identifica o autor**. Não prometa na UI "suas avaliações" enquanto a lacuna L3 não for resolvida.

### 2.3.3 Voto em avaliação (`EvaluationVote`)

| Método | Rota | Acesso | Uso |
|---|---|---|---|
| POST | `/evaluation_votes` | `user` | Votar como útil ou não útil |
| GET | `/evaluation_votes` | `user` | Descobrir o voto atual do usuário (filtros `evaluation` e `user`) |

```json
{ "evaluation": "/evaluations/01923f6e-…", "value": 1 }
```

- `value`: `1` (útil) ou `-1` (não útil). Outro valor gera **422**: "Vote value must be either 1 (upvote) or -1 (downvote)."
- **Votar de novo substitui o voto anterior** (o processador atualiza o registro existente). Cada usuário tem um voto por avaliação.
- **Não é possível retirar o voto**: não existe DELETE (lacuna L5). Na UI, o botão ativo não deve "desmarcar"; deixe claro que o voto pode ser trocado, mas não removido.
- Avaliações com `netVotes ≤ -3` são **excluídas do resumo** do local. Mostre-as recolhidas, com o aviso "Esta avaliação foi marcada como pouco útil pela comunidade".

### 2.3.4 Critérios (`CriterionEnum`)

Exposto como recurso somente leitura (`GetCollection`/`Get`; confira o caminho exato no `/docs`, provavelmente `/criterion_enums`). O backend devolve apenas o identificador e o valor, então **os rótulos e as descrições em português ficam no frontend** (`lib/criteria.ts`):

| Valor (`value`) | Rótulo curto | Descrição (tooltip/ajuda) | Ícone sugerido (lucide; confirme o nome em `lucide-react`) |
|---|---|---|---|
| `wheelchair_accessible` | Acesso para cadeira de rodas | Rampas, elevadores, portas largas e circulação livre | `Accessibility` |
| `accessible_restroom` | Banheiro acessível | Banheiro adaptado, com barras de apoio e espaço de giro | `Toilet` / `Bath` |
| `tactile_paving` | Piso tátil | Piso de alerta e direcional para pessoas com deficiência visual | `Footprints` |
| `braille_signage` | Sinalização em Braille | Placas em Braille ou alto-relevo | `Hand` |
| `sign_language` | Atendimento em Libras | Atendimento na Língua Brasileira de Sinais | `HandMetal` / `MessagesSquare` |
| `service_animal_allowed` | Animal de serviço permitido | Entrada facilitada para cão-guia e outros animais de serviço | `Dog` |

Escala de nota (0 a 10) exibida no formulário:

| Nota | Âncora textual |
|---|---|
| 0 | Não existe |
| 1–4 | Existe, mas inadequado ou impede o uso |
| 5–6 | Parcial: usável com dificuldade ou ajuda |
| 7–9 | Adequado, com pequenos problemas |
| 10 | Totalmente adequado |

### 2.3.5 Imagens e arquivos (`Image`, `File`)

| Método | Rota | Corpo | Resposta |
|---|---|---|---|
| POST | `/images` | `multipart/form-data`, campo `file` | `@id`, `contentUrl`, `contentUrlXs` (150 px), `contentUrlSm` (300 px), `contentUrlMd` (600 px), `contentUrlLg` (1200 px) |
| GET | `/images/{id}` | — | idem |
| POST | `/files` | `multipart/form-data`, campo `file` | `@id`, `contentUrl` |

> ⚠️ **Lacuna L1:** `Evaluation` ainda não tem relação com `Image`, e o texto alternativo não é armazenado. O frontend pode preparar o componente de upload, mas só deve habilitá-lo depois que o backend aceitar `images: [IRI]` e `alt` na avaliação.

### 2.3.6 Usuário (`User`)

| Método | Rota | Acesso |
|---|---|---|
| GET | `/users/{id}` | só o próprio usuário |
| GET | `/admin/users` | admin (`?name=` busca em nome e sobrenome; `itemsPerPage` controlado pelo cliente) |
| GET | `/admin/users/{id}` | admin |

Campos: `firstName`, `lastName`, `name`. Para o cabeçalho ("Olá, Marina"), prefira os dados da sessão do `better-auth` (`useSession()`), que já têm nome e e-mail.

## 2.4 Tratamento de erros

O `fetchApi` lança:
- `Error(title)` quando não há `violations`;
- `FetchError { message, status, fields }` quando há `violations`, com `fields` indexado por `propertyPath`.

| Status | Situação | O que a UI faz |
|---|---|---|
| 400 | Place ID ausente ou inválido | Mensagem no passo "Escolher local": "Não conseguimos identificar este local. Tente buscá-lo novamente." |
| 401 | Token ausente ou expirado | Salvar o rascunho, redirecionar para `/login?callbackURL=<rota atual>` |
| 403 | Sem permissão | Tela "Acesso negado" (já existe `components/admin/AccessDenied.tsx`) |
| 404 | Recurso inexistente | Página 404 com link para o mapa |
| 422 | Validação | Erro **junto ao campo** (`aria-describedby`) **e** resumo de erros no topo do formulário, com o foco movido para o resumo |
| 5xx / rede | Falha | Mensagem com botão "Tentar novamente"; manter os dados do formulário |

Mapeamento de `propertyPath` para campos do formulário: `comment` vai para o campo de comentário; `ratings[i].rating` vai para o critério de índice `i` **do array enviado**. Guarde o array enviado para fazer o mapeamento de volta.

## 2.5 Tempo real (Mercure)

O backend publica atualizações nos tópicos (IRIs absolutos):

| Recurso | Tópicos |
|---|---|
| Establishment | `…/establishments/{id}` e `…/admin/establishments/{id}` |
| Evaluation | `…/evaluations/{id}` e `…/admin/evaluations/{id}` |

A URL do hub vem no cabeçalho `Link` (`rel="mercure"`), e `fetchApi` a devolve em `hubURL`. Uso sugerido:
- **Detalhe do local:** assinar o tópico do estabelecimento e, ao receber um evento, invalidar `["establishment", id]`.
- **Mapa:** opcional. Assinar `…/establishments/{id}` com curinga, se o hub permitir, ou apenas refazer a busca ao focar a janela (`refetchOnWindowFocus`).
- **Acessibilidade:** atualizações automáticas **não** podem mover o foco nem reordenar a lista sob o cursor. Anuncie em uma região `aria-live="polite"` ("1 nova avaliação. Atualizar lista") e deixe o usuário aplicar a mudança.

## 2.6 APIs externas usadas pelo frontend

| API | Uso | Variável |
|---|---|---|
| Google Maps JavaScript API | Mapa | `NEXT_PUBLIC_GOOGLE_MAPS_API_KEY` |
| Google Places API (New): Autocomplete | Escolher o local ao avaliar e obter o `place_id` | a mesma chave, com a Places API habilitada e restrita por domínio |
| Geolocalização do navegador | Centralizar o mapa e ordenar por distância | — (pedir permissão **somente após ação do usuário**) |

Centro padrão do mapa: **Tubarão (SC), `{ lat: -28.4667, lng: -49.0069 }`**, zoom 14. O código atual usa São Paulo e deve ser trocado.
