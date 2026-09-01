"use client";

import { useSearchParams } from "next/navigation";

export default function NewEvaluationPage() {
  const searchParams = useSearchParams();
  const establishmentId = searchParams.get("establishmentId");

  return (
    <div className="p-8 max-w-2xl mx-auto">
      <h1 className="text-2xl font-bold mb-4">Adicionar Avaliação</h1>
      {establishmentId && (
        <p className="text-gray-600 mb-8">Avaliando Estabelecimento: {establishmentId}</p>
      )}
      <div className="bg-white p-6 rounded-lg shadow border border-gray-200">
        <p className="text-sm text-gray-500">Formulário de avaliação em desenvolvimento...</p>
      </div>
    </div>
  );
}
