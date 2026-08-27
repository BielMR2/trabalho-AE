# Arquitetura do Frontend (API Platform PWA)

Este documento explica como funciona a estrutura do frontend (diretório `pwa/`) gerado pelo API Platform neste projeto. O frontend utiliza tecnologias modernas do ecossistema React para proporcionar uma aplicação rápida, escalável e com integração facilitada com a API backend.

## Tecnologias Principais

1. **Next.js (App Router)**
   O projeto utiliza o framework Next.js 14/15+, tirando proveito do **App Router** (`app/`).
   - O roteamento é baseado em diretórios. Por exemplo, a pasta `app/books/` e seu arquivo `page.tsx` definem a rota `/books`.
   - Permite renderização de componentes tanto no lado do servidor (Server Components) quanto no lado do cliente (Client Components - usando `"use client"`).

2. **React Admin (`app/admin`)**
   A rota administrativa `/admin` foi construída usando o pacote `@api-platform/admin`.
   - Este pacote funciona interpretando a documentação automática da sua API (em formato Hydra/OpenAPI).
   - Ele gera as telas de CRUD (Create, Read, Update, Delete) dinamicamente com base nas entidades exportadas pela API, com necessidade mínima de configuração manual.

3. **React Query (`@tanstack/react-query`)**
   Toda a parte de consumo da API no lado do cliente, incluindo cache, revalidação e gerenciamento de estado de requisições assíncronas, é feita pelo React Query.
   - O `QueryClientProvider` está configurado globalmente no arquivo `app/providers.tsx`.

4. **Estilização (TailwindCSS e Shadcn UI)**
   - O projeto utiliza **TailwindCSS** para estilização através de classes utilitárias, permitindo o desenvolvimento rápido de interfaces.
   - Os componentes visuais reutilizáveis ficam em `components/ui/` e são baseados no **Shadcn UI**, que traz componentes acessíveis (Radix UI) que você pode customizar e integrar com Tailwind.

5. **Mercure (Real-time updates)**
   O API Platform suporta nativamente o Mercure para enviar atualizações do backend para o frontend (via Server-Sent Events).
   - No frontend, o arquivo `utils/mercure.ts` lida com a subscrição dos tópicos (URLs dos recursos) para que a interface reaja e atualize automaticamente quando um dado for modificado por outro usuário.

## Estrutura de Diretórios

A pasta `pwa/` está dividida nas seguintes responsabilidades:

- **`app/`**: Contém todas as rotas da aplicação, layouts globais (`layout.tsx`) e a página inicial (`page.tsx`).
- **`components/`**: Contém os componentes React, divididos em pastas por domínio (ex: componentes de livros, etc.) e a pasta `ui/` (Shadcn UI).
- **`types/`**: Contém as definições e interfaces TypeScript que espelham o formato dos dados retornados pelo backend da API Platform.
- **`utils/`**: Funções auxiliares gerais, incluindo utilitários de fetch de dados, tratamento de erros e integração com o Mercure.
- **`config/`**: Arquivos de configuração relacionados à API (pontos de entrada) ou autenticação (ex: integração com provedores OAuth/Keycloak).

## Fluxo de Dados (Data Fetching)

Quando você precisa exibir dados de uma entidade da API:
1. Um **Server Component** (Next.js) ou o **React Query** (no lado do cliente) faz um fetch na URL correspondente (ex: `/api/books`).
2. Os dados recebidos (no formato JSON-LD/Hydra) são mapeados de acordo com os tipos definidos na pasta `types/`.
3. Os componentes visuais (em `components/`) utilizam esses dados para montar a interface.
4. Qualquer mutação (POST/PUT/DELETE) é seguida pela invalidação do cache do React Query, forçando a interface a buscar os dados atualizados no backend.
