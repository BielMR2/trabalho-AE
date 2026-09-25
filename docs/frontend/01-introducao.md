# 1. Introdução e visão do produto

## 1.1 Contexto

Com o avanço da sociedade, a empatia e a inclusão de pessoas com deficiência se tornam pautas cada vez mais urgentes. A realidade, porém, muitas vezes contrasta com essa necessidade: a pouca atenção da sociedade às pessoas com deficiência e a falta de acessibilidade em espaços públicos e privados são um problema social relevante na cidade de Tubarão (SC).

Este projeto de extensão, alinhado à missão do IFSC de promover a inclusão e a transformação social, trata da escassez de acessibilidade em Tubarão. Seu objetivo é identificar e analisar a falta de acessibilidade em locais públicos e privados e propor soluções práticas para o problema. A partir dessa análise, o projeto oferece sugestões e desenvolve um protótipo de software, o **Acessibiliza**, para contribuir com um ambiente mais justo, equitativo e inclusivo.

## 1.2 Justificativa

- **Decreto nº 6.949/2009**, que promulga a Convenção Internacional sobre os Direitos das Pessoas com Deficiência. Ele adota o **desenho universal**: "produtos, ambientes, programas e serviços devem ser concebidos para serem utilizados, na maior medida possível, por todas as pessoas, sem necessidade de adaptação ou projeto específico". Também define a **adaptação razoável**: as modificações e os ajustes necessários e adequados que não acarretem ônus desproporcional.
- **Lei nº 13.146/2015 (Lei Brasileira de Inclusão, LBI)** reforça o dever do poder público e da sociedade de eliminar barreiras. O **art. 63** torna obrigatória a acessibilidade dos sítios da internet mantidos por empresas ou órgãos públicos, o que também vale para este aplicativo.
- **Censo 2022 (IBGE):** cerca de 18,6 milhões de pessoas com deficiência no Brasil. Das 174,2 milhões de pessoas em áreas urbanas, cerca de 119,9 milhões (68,8%) vivem em ruas sem rampa para cadeirantes.
- **Demanda local:** a comunidade externa ao IFSC Câmpus Tubarão não tem um canal prático para registrar e comunicar barreiras de acessibilidade.

> Consequência para o frontend: um aplicativo sobre acessibilidade que não seja acessível contradiz o próprio propósito. A acessibilidade da interface é **requisito funcional**, não melhoria opcional. Veja [05-acessibilidade.md](05-acessibilidade.md).

## 1.3 Objetivos e como o frontend os atende

| Objetivo do projeto | Como o frontend atende | Suporte no backend hoje |
|---|---|---|
| **Desenvolvimento da plataforma:** anexar fotos, adicionar descrições e categorizar problemas | Formulário de avaliação com notas por critério, comentário e envio de fotos | Critérios e comentário: **sim**. Upload de imagem (`POST /images`): **sim**, mas a imagem **não é vinculada** à avaliação (lacuna L1) |
| **Engajamento e mapeamento colaborativo** | Mapa público, avaliação em poucos passos, votos de utilidade, compartilhamento de locais | **Sim** (`/evaluations`, `/evaluation_votes`) |
| **Análise de dados e relatórios** | Painel com indicadores, gráficos e ranking por critério | **Parcial:** `evaluationsSummary` por local; não há endpoint agregado (lacuna L4) |

## 1.4 Público-alvo e personas

As personas orientam decisões de interface. Toda tela deve funcionar para **todas** elas.

| Persona | Perfil | Tecnologia assistiva ou contexto | Necessidade principal |
|---|---|---|---|
| **Marina, 34 anos** | Usuária de cadeira de rodas | Celular Android, uso com uma mão | Saber antes de sair de casa se um local tem rampa e banheiro adaptado |
| **Carlos, 52 anos** | Cego | iPhone com VoiceOver; desktop com NVDA | Achar locais **sem usar o mapa visual**; saber se há piso tátil, Braille e se o cão-guia é aceito |
| **Júlia, 22 anos** | Surda, usuária de Libras | Celular; português como segunda língua | Saber onde há atendimento em Libras; textos simples; conteúdo sem depender de áudio |
| **Seu Antônio, 71 anos** | Baixa visão e tremor nas mãos | Tablet com fonte ampliada (200%) | Alvos de toque grandes, alto contraste, poucas etapas |
| **Pedro, 17 anos** | Estudante do IFSC, voluntário | Celular | Avaliar muitos locais rapidamente durante mutirões de mapeamento |
| **Luísa, 45 anos** | Servidora da prefeitura, conselho municipal | Desktop | Ver o panorama do município, comparar critérios e exportar dados para relatórios |
| **Admin do projeto** | Professor ou bolsista do IFSC | Desktop | Moderar avaliações abusivas e corrigir cadastros de estabelecimentos |

## 1.5 Requisitos funcionais (RF)

| ID | Requisito | Prioridade | Endpoint |
|---|---|---|---|
| RF01 | Exibir estabelecimentos avaliados em um mapa centrado em Tubarão (SC) | Alta | `GET /establishments` |
| RF02 | Exibir os mesmos estabelecimentos em **lista** acessível (alternativa ao mapa) | Alta | `GET /establishments` |
| RF03 | Buscar por nome e por endereço | Alta | `?name=`, `?address=` |
| RF04 | Filtrar por classificação (Bom/Médio/Ruim) em cada critério | Alta | `?criterion_average[<criterio>]=` |
| RF05 | Ver detalhes do local: dados de contato, resumo por critério, avaliações | Alta | `GET /establishments/{id}` |
| RF06 | Entrar e sair com a conta Keycloak | Alta | OIDC |
| RF07 | Avaliar um local (existente ou novo, via Google Places) com notas de 0 a 10 por critério e comentário opcional | Alta | `POST /evaluations` |
| RF08 | Informar o motivo quando o comentário for rejeitado pela moderação | Alta | resposta 422 |
| RF09 | Votar se uma avaliação foi útil (+1) ou não (-1) e poder trocar o voto | Média | `POST /evaluation_votes` |
| RF10 | Anexar fotos à avaliação, com texto alternativo | Média | `POST /images` + **lacuna L1** |
| RF11 | Painel de dados do município: totais, médias por critério, distribuição Bom/Médio/Ruim, ranking | Média | agregação no cliente ou **lacuna L4** |
| RF12 | Exportar dados do painel em CSV | Baixa | cliente |
| RF13 | Atualizar a tela em tempo real quando surgirem novas avaliações | Baixa | Mercure |
| RF14 | Área administrativa: editar e desativar estabelecimentos e avaliações, listar usuários | Média | `/admin/*` |
| RF15 | Página "Minhas avaliações" | Baixa | **lacuna L3** |
| RF16 | Página de preferências de acessibilidade (tamanho de fonte, contraste, movimento) | Alta | somente cliente |
| RF17 | Páginas "Sobre o projeto" e "Declaração de acessibilidade" | Alta | estática |

## 1.6 Requisitos não funcionais (RNF)

| ID | Requisito |
|---|---|
| RNF01 | Conformidade com **WCAG 2.2 nível AA**, **ABNT NBR 17225:2025** e **eMAG 3.1** |
| RNF02 | **Mobile first**: projetar a partir de 320 px de largura, sem rolagem horizontal (WCAG 1.4.10) |
| RNF03 | Idioma `pt-BR` em toda a interface (`<html lang="pt-BR">`) |
| RNF04 | Tempo de carregamento da tela inicial em 4G abaixo de 3 s (LCP) e INP abaixo de 200 ms |
| RNF05 | Funcionar nos navegadores atuais (Chrome, Firefox, Safari, Edge) e com leitores de tela (NVDA, JAWS, VoiceOver, TalkBack) |
| RNF06 | Nenhum dado pessoal exposto publicamente (a API não expõe o autor da avaliação) — respeitar a LGPD |
| RNF07 | Sem `console.log` em código versionado; ESLint sem erros (`pnpm lint`) |
| RNF08 | Tipos TypeScript alinhados ao OpenAPI do backend (fonte única da verdade) |
