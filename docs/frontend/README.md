# Acessibiliza — Documentação do Frontend

Documentação para o desenvolvimento do frontend do **Acessibiliza**, aplicativo de mapeamento colaborativo de acessibilidade em estabelecimentos de Tubarão (SC), projeto de extensão do IFSC Câmpus Tubarão.

Esta documentação foi escrita a partir do backend existente em `api/` (API Platform 4.3 + Symfony 8) e do esqueleto de frontend em `pwa/` (Next.js 16 + React 19). Tudo o que ela diz sobre endpoints, campos e regras vem do código do backend; o que ainda **não existe** no backend está marcado como **lacuna**.

## Índice

| # | Documento | Conteúdo |
|---|-----------|----------|
| 1 | [Introdução e visão do produto](01-introducao.md) | Contexto, justificativa, objetivos, público-alvo, personas e requisitos |
| 2 | [Contrato da API](02-api.md) | Endpoints, modelos de dados, filtros, autenticação, erros, tempo real |
| 3 | [Telas](03-telas.md) | Mapa de rotas, especificação de cada tela, estados e wireframes |
| 4 | [Fluxos](04-fluxos.md) | Fluxos de navegação e de dados (diagramas) |
| 5 | [Acessibilidade](05-acessibilidade.md) | Normas, recursos obrigatórios, padrões por componente, checklist e testes |
| 6 | [Arquitetura e padrões de implementação](06-arquitetura.md) | Estrutura de pastas, tipos, React Query, design system, testes |
| 7 | [Lacunas do backend e roadmap](07-lacunas-e-roadmap.md) | O que falta na API para cumprir os objetivos e ordem sugerida de entrega |

## Resumo em uma página

- **O que o usuário faz:** encontra locais no mapa (ou na lista), vê como cada um foi avaliado em 6 critérios de acessibilidade, avalia um local (notas de 0 a 10 por critério e um comentário), vota se as avaliações dos outros são úteis e consulta um painel com os dados do município.
- **Critérios avaliados** (`CriterionEnum`): acesso para cadeira de rodas, banheiro acessível, piso tátil, sinalização em Braille, atendimento em Libras e entrada de animais de serviço.
- **Classificação:** média `< 5` = **Ruim**, `5 a < 7` = **Médio**, `≥ 7` = **Bom** (as mesmas faixas do filtro `criterion_average` da API).
- **Login:** Keycloak (OIDC). Ver e filtrar é público; avaliar e votar exigem login; moderar exige o papel de administrador.
- **Local novo:** o usuário escolhe o lugar pelo Google Places. O backend cria o estabelecimento na primeira avaliação, a partir do `googlePlaceId`.
- **Moderação:** a OpenAI analisa cada comentário antes de salvar, e as avaliações com saldo de votos `≤ -3` saem do resumo do local.
- **Meta de acessibilidade do próprio app:** WCAG 2.2 nível AA, ABNT NBR 17225 e eMAG. Todo conteúdo do mapa precisa ter uma alternativa em lista.

## Stack do frontend (já configurada em `pwa/`)

| Camada | Tecnologia |
|--------|-----------|
| Framework | Next.js 16 (App Router), React 19, TypeScript |
| Dados do servidor | TanStack React Query 5 |
| UI | Tailwind CSS 4, componentes Shadcn/Base UI em `pwa/components/ui/`, ícones `lucide-react` |
| Mapa | `@vis.gl/react-google-maps` (Google Maps JS API + Places) |
| Autenticação | `better-auth` com provedor OAuth genérico (Keycloak) |
| Formulários | `react-hook-form` + `yup` |
| Painel admin | React Admin 5 + `@api-platform/admin` |
| Tempo real | Mercure (SSE), via `pwa/utils/mercure.ts` |
