"use client";

import { useSearchParams } from "next/navigation";
import { useSession } from "../../hooks/useAuth";

export default function EvaluationPage() {
  const searchParams = useSearchParams();
  const establishmentId = searchParams.get("establishment");
  const { session, isPending } = useSession();

  if (isPending) {
    return <div className="p-8">Verificando autenticação...</div>;
  }

  if (!session) {
    return <div className="p-8">Você precisa estar logado para avaliar. (Redirecionamento falhou ou foi bloqueado)</div>;
  }

  return (
    <div className="p-8 max-w-2xl mx-auto">
      <h1 className="text-2xl font-bold mb-4">Adicionar Avaliação</h1>
      <p className="mb-4">
        Estabelecimento ID: <code className="bg-muted p-1 rounded">{establishmentId || "Nenhum selecionado"}</code>
      </p>
      
      <div className="bg-card border rounded-lg p-6 shadow-sm">
        <p className="text-muted-foreground">
          O formulário completo de avaliações (com critérios dinâmicos e estrelas) 
          será renderizado aqui nas próximas iterações.
        </p>
        <p className="mt-4">
          Usuário atual: <strong>{session.user.name}</strong>
        </p>
      </div>
    </div>
  );
}

