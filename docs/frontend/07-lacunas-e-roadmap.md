# 7. Lacunas do backend e roadmap

## 7.1 Lacunas encontradas no backend

Pontos em que a API atual (`api/src/`) não sustenta um requisito da documentação ou tem um comportamento que afeta o frontend. Cada item diz o que o frontend faz enquanto a lacuna existir.

| ID | Lacuna | Impacto | Proposta no backend | Enquanto isso, no frontend |
|---|---|---|---|---|
| **L1** | `Evaluation` não tem relação com `Image`, e a imagem não guarda texto alternativo. `POST /images` e `POST /files` também não têm regra de segurança (qualquer pessoa pode enviar) | Objetivo "anexar fotos" (RF10) não atendido | Adicionar `Evaluation.images` (ManyToMany com `Image`), o campo `Image.alt` (obrigatório), `security: is_granted("OIDC_USER")` no upload e limites de tamanho e tipo (`Assert\Image`) | Componente de upload pronto, atrás de uma *feature flag* desligada |
| **L2** | `createdAt`, `updatedAt` e `active` só aparecem nos grupos admin, e as rotas públicas **não filtram `active = false`** (avaliações de estabelecimentos removidos ou desativados continuam aparecendo) | Não dá para ordenar por data nem mostrar "há 3 dias"; moderação por desativação não funciona | Expor `createdAt` em `Evaluation:read`; criar uma *Doctrine extension* que filtre `active = true` nas rotas públicas | Ordenar só por "Mais úteis"; moderar com DELETE |
| **L3** | Não existe vínculo autor → avaliação (`Evaluation` não tem `user`) | Sem "Minhas avaliações" (RF15); sem limite de uma avaliação por pessoa por local | Adicionar `Evaluation.author` (preenchido pelo processador, **sem** expor dados pessoais publicamente) e um filtro `?author=me` / `GET /me/evaluations` | T8 fora do menu |
| **L4** | Não há endpoint agregado para relatórios | O painel (RF11) precisa baixar todos os locais | `GET /stats` com totais, médias por critério e distribuição por situação, com filtro por bairro/endereço | Agregar no cliente |
| **L5** | Não dá para retirar o voto (sem DELETE em `/evaluation_votes`) | UX de voto limitada | `Delete` com `security: object.user == user` | Voto pode ser trocado, não removido |
| **L6** | `CriterionAverageFilter` usa todas as avaliações, enquanto `evaluationsSummary` ignora as de saldo `≤ -3` | Resultado do filtro pode divergir da classificação exibida | Aplicar a mesma regra de saldo no subquery do filtro | Exibir sempre `evaluationsSummary` |
| **L7** | Paginação fixa em 30, sem `itemsPerPage` para estabelecimentos; o mapa precisa de todos os pontos | Várias requisições para o mapa | `paginationClientItemsPerPage: true` (com máximo) ou um endpoint leve `GET /establishments/map` (só `@id`, `name`, `location`, situação geral) | Buscar as páginas em sequência, com limite |
| **L8** | Os recursos admin de `Establishment` e `Evaluation` só têm PATCH e DELETE, sem listagem nem visualização | O React Admin não consegue listar para moderar | Adicionar `GetCollection` e `Get` em `/admin/establishments` e `/admin/evaluations` | Listar pelos endpoints públicos e agir pelos `/admin/...` |
| **L9** | Grupos de serialização inconsistentes: `Establishments:read` / `Establishments:read:admin` (plural) em `Evaluation`, `Image` e `File` versus `Establishment:read:admin` (singular) em `Establishment`; o `id` do estabelecimento só está em `Establishment:read:admin`, que o recurso admin não usa | Campos podem faltar nas respostas admin | Padronizar os nomes dos grupos | Usar `@id` (IRI), que sempre vem |
| **L10** | Não há denúncia de avaliação | Moderação depende só de votos e da OpenAI | `POST /evaluation_reports` (motivo) | Link "Denunciar" fora do escopo inicial |
| **L11** | Só existem critérios por **estabelecimento**. Barreiras em vias públicas (calçada sem rampa, buraco, falta de sinalização em rua), citadas nos objetivos, não têm onde ser registradas | Parte do objetivo "categorizar problemas" | Novo recurso `Report` (ponto no mapa + categoria + foto + descrição), independente de Google Place | Fora do escopo do MVP; prever espaço no menu ("Relatar barreira na rua") |
| **L12** | `comment` sem limite de tamanho | Textos enormes quebram o layout | `Assert\Length(max: 1000)` | Limite de 1000 no cliente |
| **L13** | Avaliação só é possível para locais com `googlePlaceId` | Locais que não estão no Google não podem ser avaliados | Permitir criar com nome + coordenadas (sujeito a moderação) | Mensagem explicando a limitação |

## 7.2 Roadmap sugerido do frontend

### Marco 1: MVP de consulta (RF01–RF06, RF16, RF17)
- [ ] Limpeza do demo: `lang="pt-BR"`, título, textos, callback de login, recursos admin
- [ ] Layout acessível: skip link, landmarks, cabeçalho, rodapé, anunciador, `RouteAnnouncer`
- [ ] `lib/criteria.ts`, `lib/rating.ts`, `lib/geo.ts`, `lib/hydra.ts`
- [ ] T1 Explorar: lista + mapa centrado em Tubarão, filtros acessíveis na URL, cartão-resumo, legenda
- [ ] T2 Detalhe: resumo em tabela, lista de avaliações
- [ ] Painel de preferências de acessibilidade + VLibras
- [ ] T5 Sobre, T6 Declaração de acessibilidade, T10 erros
- [ ] `eslint-plugin-jsx-a11y` + axe nos testes E2E

### Marco 2: Contribuição (RF07–RF09, RF13)
- [ ] T3 Assistente de avaliação com combobox do Places
- [ ] Rascunho + login no meio do fluxo
- [ ] Tratamento de 400/401/422 (moderação)
- [ ] Votos otimistas
- [ ] Atualização via Mercure no detalhe

### Marco 3: Análise de dados (RF11, RF12)
- [ ] T4 Painel: KPIs, gráficos acessíveis com tabela equivalente, ranking, CSV
- [ ] (backend) L4 `GET /stats` e L7

### Marco 4: Evolução (dependem do backend)
- [ ] Fotos com texto alternativo (L1)
- [ ] Minhas avaliações (L3) e ordenação por data (L2)
- [ ] Área admin completa (L8)
- [ ] Relato de barreiras em vias públicas (L11)
- [ ] PWA instalável / modo offline de consulta

### Validação contínua
- Testes com usuários com deficiência ao fim de cada marco (parcerias locais e NAPNE/IFSC).
- Auditoria manual WCAG 2.2 AA antes de cada divulgação pública, com a Declaração de Acessibilidade atualizada.
