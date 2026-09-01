"use client";

import { Establishment } from "@/types/Establishment";
import {
  Drawer,
  DrawerContent,
  DrawerHeader,
  DrawerTitle,
  DrawerDescription,
  DrawerFooter,
  DrawerClose,
} from "@/components/ui/drawer";
import { Button } from "@/components/ui/button";
import { MapPin, Phone, Globe, Star } from "lucide-react";
import { signInWithKeycloak, useSession } from "@/hooks/useAuth";
import { useRouter } from "next/navigation";

const CRITERION_LABELS: Record<string, string> = {
  wheelchair_accessible: "Cadeira de Rodas",
  accessible_restroom: "Banheiro Acessível",
  tactile_paving: "Piso Tátil",
  braille_signage: "Sinalização Braille",
  sign_language: "Libras",
  service_animal_allowed: "Animais de Serviço",
};

function getRatingColor(average: number): string {
  if (average >= 7) return "bg-green-100 text-green-800";
  if (average >= 5) return "bg-yellow-100 text-yellow-800";
  return "bg-red-100 text-red-800";
}

interface EstablishmentDrawerProps {
  establishment: Establishment | null;
  onClose: () => void;
}

export default function EstablishmentDrawer({ establishment, onClose }: EstablishmentDrawerProps) {
  const { data: session, isPending } = useSession();
  const router = useRouter();

  const handleAddEvaluation = async () => {
    if (!establishment || isPending) return;
    
    const params = new URLSearchParams();
    if (establishment.googlePlaceId) params.set("placeId", establishment.googlePlaceId);
    if (establishment.name) params.set("name", establishment.name);
    
    const evaluateUrl = `/evaluate?${params.toString()}`;
    
    if (!session) {
      try {
        await signInWithKeycloak(`${window.location.origin}${evaluateUrl}`);
      } catch (e) {
        console.error("Login redirect failed:", e);
      }
    } else {
      router.push(evaluateUrl);
    }
  };

  return (
    <Drawer open={!!establishment} onOpenChange={(open) => !open && onClose()}>
      <DrawerContent>
        <div className="mx-auto w-full max-w-lg">
          <DrawerHeader>
            <DrawerTitle className="text-left">{establishment?.name}</DrawerTitle>
            <DrawerDescription className="flex items-center gap-1 text-left">
              <MapPin className="w-4 h-4 flex-shrink-0" />
              {establishment?.address || "Sem endereço cadastrado"}
            </DrawerDescription>
          </DrawerHeader>

          <div className="p-4 pt-0 space-y-4">
            {/* Contact info */}
            {(establishment?.phoneNumber || establishment?.website) && (
              <div className="flex flex-wrap gap-3 text-sm text-gray-600">
                {establishment.phoneNumber && (
                  <a href={`tel:${establishment.phoneNumber}`} className="flex items-center gap-1 hover:text-cyan-700">
                    <Phone className="w-3.5 h-3.5" />
                    {establishment.phoneNumber}
                  </a>
                )}
                {establishment.website && (
                  <a href={establishment.website} target="_blank" rel="noopener noreferrer" className="flex items-center gap-1 hover:text-cyan-700">
                    <Globe className="w-3.5 h-3.5" />
                    Website
                  </a>
                )}
              </div>
            )}

            {/* Evaluation Summary */}
            {establishment?.evaluationsSummary && Object.keys(establishment.evaluationsSummary).length > 0 && (
              <div>
                <h4 className="text-sm font-semibold text-gray-700 mb-2">Avaliações de Acessibilidade</h4>
                <div className="grid grid-cols-2 gap-2">
                  {Object.entries(establishment.evaluationsSummary).map(([criterion, data]) => (
                    <div
                      key={criterion}
                      className={`flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium ${getRatingColor(data.average)}`}
                    >
                      <span>{CRITERION_LABELS[criterion] || criterion}</span>
                      <span className="flex items-center gap-0.5">
                        <Star className="w-3 h-3 fill-current" />
                        {data.average.toFixed(1)}
                      </span>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Add evaluation button */}
            <Button onClick={handleAddEvaluation} disabled={isPending} className="w-full bg-cyan-700 hover:bg-cyan-800 text-white">
              + Adicionar Avaliação
            </Button>
          </div>

          <DrawerFooter>
            <DrawerClose render={<Button variant="outline" />}>
              Fechar
            </DrawerClose>
          </DrawerFooter>
        </div>
      </DrawerContent>
    </Drawer>
  );
}

