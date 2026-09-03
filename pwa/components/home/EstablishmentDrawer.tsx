"use client";

import { useQuery } from "@tanstack/react-query";
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription } from "../ui/sheet";
import { Button } from "../ui/button";
import { Establishment, Evaluation } from "../../types/Establishment";
import { fetchApi } from "../../utils/dataAccess";
import { useSession, signInWithKeycloak } from "../../hooks/useAuth";
import { useRouter } from "next/navigation";
import { Loader2, Plus, Star } from "lucide-react";

interface EstablishmentDrawerProps {
  establishmentId: string | null;
  onClose: () => void;
}

export function EstablishmentDrawer({ establishmentId, onClose }: EstablishmentDrawerProps) {
  const router = useRouter();
  const { session } = useSession();

  const { data, isLoading } = useQuery({
    queryKey: ["establishment", establishmentId],
    queryFn: async () => {
      if (!establishmentId) return null;
      const res = await fetchApi<Establishment>(establishmentId);
      return res?.data;
    },
    enabled: !!establishmentId,
  });

  const handleAddEvaluation = async () => {
    if (!establishmentId) return;
    const evalUrl = `/evaluation?establishment=${encodeURIComponent(establishmentId)}`;
    if (session) {
      router.push(evalUrl);
    } else {
      await signInWithKeycloak(window.location.origin + evalUrl);
    }
  };

  return (
    <Sheet open={!!establishmentId} onOpenChange={(open) => !open && onClose()}>
      <SheetContent side="right" className="w-full sm:max-w-md overflow-y-auto bg-white shadow-xl border-l border-border">
        {isLoading ? (
          <div className="flex h-full items-center justify-center">
            <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
          </div>
        ) : !data ? (
          <div className="flex h-full items-center justify-center text-muted-foreground">
            Estabelecimento não encontrado.
          </div>
        ) : (
          <div className="flex flex-col gap-6 pt-6">
            <SheetHeader>
              <SheetTitle className="text-2xl">{data.name}</SheetTitle>
              <SheetDescription>{data.address}</SheetDescription>
            </SheetHeader>

            {data.evaluationsSummary && Object.keys(data.evaluationsSummary).length > 0 && (
              <div className="space-y-3">
                <h3 className="text-sm font-semibold text-muted-foreground uppercase">Média de Critérios</h3>
                <div className="grid gap-2">
                  {Object.entries(data.evaluationsSummary).map(([criterion, summary]) => (
                    <div key={criterion} className="flex justify-between items-center text-sm bg-muted/50 p-2 rounded-md">
                      <span>{criterion}</span>
                      <span className="font-medium flex items-center gap-1">
                        <Star className="h-3 w-3 fill-primary text-primary" />
                        {summary.average.toFixed(1)} ({summary.count})
                      </span>
                    </div>
                  ))}
                </div>
              </div>
            )}

            <Button onClick={handleAddEvaluation} className="w-full gap-2">
              <Plus className="h-4 w-4" />
              Adicionar Avaliação
            </Button>

            <div className="space-y-4">
              <h3 className="text-sm font-semibold text-muted-foreground uppercase">Avaliações dos Usuários</h3>
              {data.evaluations && data.evaluations.length > 0 ? (
                <div className="grid gap-4">
                  {data.evaluations.map((evaluation: Evaluation, idx) => (
                    <div key={evaluation["@id"] || idx} className="rounded-lg border border-border bg-white p-4 space-y-3 shadow-sm">
                      <p className="text-sm text-text-primary italic">
                        &quot;{evaluation.comment || "Sem comentário."}&quot;
                      </p>
                      {evaluation.ratings && evaluation.ratings.length > 0 && (
                        <div className="flex flex-wrap gap-2">
                          {evaluation.ratings.map((rating, rIdx) => {
                            const criterionName = typeof rating.criterion === 'string' 
                              ? rating.criterion.split('/').pop() 
                              : rating.criterion;
                            return (
                              <span key={rIdx} className="inline-flex items-center rounded-full border border-border px-2.5 py-0.5 text-xs font-semibold text-text-secondary bg-surface transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                                {criterionName}: {rating.rating}/10
                              </span>
                            );
                          })}
                        </div>
                      )}
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-sm text-muted-foreground">Nenhuma avaliação encontrada.</p>
              )}
            </div>
          </div>
        )}
      </SheetContent>
    </Sheet>
  );
}
