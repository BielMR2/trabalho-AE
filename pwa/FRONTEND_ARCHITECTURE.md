# FRONTEND_ARCHITECTURE.md — PWA da Plataforma de Avaliação de Acessibilidade

> **Propósito**: Arquivo de contexto para assistentes de IA. Contém a arquitetura completa do frontend da aplicação (Next.js), 
> estrutura de diretórios, padrões de estado, autenticação e convenções. Leia este arquivo antes de gerar código.

---

## Stack Tecnológica

| Tecnologia | Versão | Descrição / Uso |
|---|---|---|
| **Next.js** | 16.2 | Framework principal usando o App Router (`app/`). |
| **React** | 19.2 | Biblioteca de UI principal. |
| **TypeScript** | 6.0 | Tipagem estática em toda a aplicação. |
| **Tailwind CSS** | 4.3 | Estilização utilitária principal. |
| **shadcn/ui** | N/A | Componentes base de UI (`components/ui`). |
| **React Query** | 5.101 | Gerenciamento de estado de servidor e data fetching (`@tanstack/react-query`). |
| **better-auth** | 1.6 | Autenticação no frontend (com armazenamento em PostgreSQL). Integra com Keycloak via OIDC. |
| **React Admin** | 5.15 | Backoffice automatizado (`@api-platform/admin`). Usa Material UI. |
| **Vis.gl Maps** | 1.9 | Integração com o Google Maps na home (`@vis.gl/react-google-maps`). |
| **Formik / RHF** | - | Gerenciamento de formulários (`react-hook-form` é preferido para novos componentes). |

---

## Estrutura de Diretórios

O projeto segue a arquitetura de **App Router** do Next.js.

```text
pwa/
├── app/                  # Next.js App Router (Rotas e Layouts)
│   ├── admin/            # Backoffice (React Admin / API Platform Admin)
│   ├── api/auth/         # Endpoints dinâmicos do better-auth
│   ├── login/            # Páginas customizadas de autenticação
│   ├── layout.tsx        # Root layout e injeção de Providers
│   ├── page.tsx          # Homepage (Mapa + Filtros)
│   └── providers.tsx     # Context Providers (QueryClient, Auth, etc)
├── components/           # Componentes React isolados
│   ├── admin/            # Componentes específicos do React Admin (customizados)
│   ├── common/           # Header, Footer, Error, Loading, Layout
│   ├── home/             # MapView, FilterSidebar, EstablishmentDrawer
│   └── ui/               # Componentes genéricos gerados pelo shadcn/ui
├── config/               # Configurações estáticas (ex: Keycloak, API URLs)
├── hooks/                # Custom React Hooks (`useAuth`, etc)
├── lib/                  # Bibliotecas internas (instância do auth, JWT)
├── public/               # Assets estáticos
├── styles/               # CSS global (Tailwind imports)
├── types/                # Interfaces TypeScript mapeando o domínio da API
└── utils/                # Helpers, fetchers (`api.ts`, `dataAccess.ts`, `mercure.ts`)
```

---

## Fluxos Principais

### 1. Home e Mapa Interativo (`app/page.tsx`)
A página inicial consiste em 3 grandes componentes interligados via estado local e URL:
- **`FilterSidebar`**: Barra lateral contendo filtros de nome, endereço e média de critérios de acessibilidade.
- **`MapView`**: Mapa renderizado com `@vis.gl/react-google-maps`. Mostra os pins de estabelecimentos baseados nos dados buscados na API.
- **`EstablishmentDrawer`**: Uma gaveta/sheet (via shadcn/ui) que abre ao clicar em um pin no mapa, exibindo detalhes e avaliações do estabelecimento.

O estado do mapa e dos filtros alimenta o `useQuery`, que refaz a busca na API Platform de forma reativa.

### 2. Autenticação (better-auth + Keycloak)
**Arquivo**: `lib/auth.ts`
- O frontend usa a biblioteca `better-auth` que possui seu próprio banco PostgreSQL (`pg`) para armazenar sessões, accounts e users.
- Para o login real, a aplicação delega ao **Keycloak** usando o plugin `genericOAuth`.
- O JWT de acesso retornado pelo Keycloak é passado para a API Platform em chamadas que requerem autenticação (como POST de avaliações).
- Os hooks em `hooks/useAuth` facilitam a injeção do token JWT nas requisições.

### 3. Comunicação com a API e Realtime (Mercure)
**Utilitários**: `utils/dataAccess.ts` e `utils/mercure.ts`
- O data fetching primário para dados da aplicação usa `@tanstack/react-query`.
- O Next.js gerencia as chamadas baseadas no formato Hydra (JSON-LD) exposto pela API Platform.
- Listas (como os estabelecimentos no mapa) escutam o Hub Mercure através do hook customizado `useMercure(response?.data, response?.hubURL)`. Isso garante que o mapa atualize em realtime se um novo lugar for avaliado.

### 4. Admin (Backoffice)
**Pasta**: `app/admin/page.tsx`
- Roda de forma isolada na rota `/admin`.
- Diferente do resto do site, é renderizado inteiramente no client-side (`ssr: false`).
- Usa o `@api-platform/admin` que converte a documentação OpenAPI/Hydra em um CMS dinâmico instantâneo.
- O admin captura o Access Token OIDC para gerenciar permissões (`OIDC_ADMIN`).
> **⚠️ Nota Técnica**: O admin atual em `components/admin` ainda pode possuir referências a um domínio legado (ex: `books`, `reviews`). A arquitetura da API migrou para `Establishment` e `Evaluation`, então componentes legacy devem ser ignorados ou refatorados conforme necessário.

---

## Padrões e Convenções (Guia para IA)

### Ao gerar código para este Frontend:

1. **Server Components vs Client Components**:
   - Por padrão, os componentes no `app/` são Server Components.
   - Use `"use client";` no topo do arquivo estritamente quando precisar de interatividade (hooks `useState`, `useEffect`, onClick) ou consumir Context/React Query.
2. **Estilização**:
   - Utilize sempre **Tailwind CSS**. Evite criar arquivos `.css` ou CSS-in-JS puros.
   - Para componentes reaproveitáveis, verifique se já existe um em `components/ui/` (shadcn/ui) antes de criar um do zero.
3. **Data Fetching**:
   - Para busca de dados dinâmicos do client, use sempre o **React Query (`useQuery`, `useMutation`)**. Evite `useEffect` + `fetch` manuais.
   - Use as funções utilitárias pré-existentes em `utils/dataAccess.ts` para chamadas padronizadas que lidam com autenticação e headers do Hydra.
4. **Tipagem (TypeScript)**:
   - Todo componente deve ter suas props tipadas via `interface` ou `type`.
   - Reutilize interfaces da pasta `types/` sempre que o dado vier da API (ex: `Establishment`, `Evaluation`, etc).
5. **Gerenciamento de Filtros e Estado**:
   - **NUNCA** construa URLs ou controle os filtros da API de forma puramente manual (ex: strings concatenadas com `useState` puro). 
   - Utilize o **React Query** de forma idiomática para lidar com filtros e paginação, passando os filtros como variáveis/chaves no `queryKey`. O React Query deve ser a fonte de verdade para requisições de estado de servidor baseadas em parâmetros.
6. **Autenticação**:
   - Para saber se o usuário está logado, utilize a API do `better-auth` (através de hooks ou `auth.getSession()`).
   - Requisições seguras para a API precisam anexar o `Bearer {accessToken}` do Keycloak gerido pelo auth client.
7. **Integração com a API Platform**:
   - Lembre-se que as listas retornam sob a chave `"hydra:member"` e `"hydra:totalItems"`.
   - Associações são identificadas via **IRIs** (ex: `"/establishments/UUID"`), e não IDs puros numéricos ou JSON aninhado completo na maioria dos POSTs.

