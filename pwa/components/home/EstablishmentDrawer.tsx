"use client";

import { Establishment } from "@/types/Establishment";
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerFooter } from "@/components/ui/drawer";
import { Button } from "@/components/ui/button";
import { Accessibility, Bath, Footprints, BookOpen, Hand, Dog, MapPin, Phone, Globe, Star } from "lucide-react";
import { Separator } from "@/components/ui/separator";

interface EstablishmentDrawerProps {
  establishment: Establishment | null;
  onClose: () => void;
}

const CRITERION_MAP: Record<string, { label: string; Icon: any }> = {
  wheelchair_accessible: { label: "Cadeira de Rodas", Icon: Accessibility },
  accessible_restroom: { label: "Banheiro Acessível", Icon: Bath },
  tactile_paving: { label: "Piso Tátil", Icon: Footprints },
  braille_signage: { label: "Sinalização Braille", Icon: BookOpen },
  sign_language: { label: "Libras", Icon: Hand },
  service_animal_allowed: { label: "Animais de Serviço", Icon: Dog },
};

export default function EstablishmentDrawer({ establishment, onClose }: EstablishmentDrawerProps) {
  if (!establishment) return null;

  const getColorClass = (val: number) => {
    if (val >= 7) return "bg-success";
    if (val >= 5) return "bg-warning";
    return "bg-danger";
  };

  const getTextColorClass = (val: number) => {
    if (val >= 7) return "text-success";
    if (val >= 5) return "text-warning";
    return "text-danger";
  };

  return (
    <Drawer open={!!establishment} onOpenChange={(open) => !open && onClose()}>
      <DrawerContent className="bg-surface max-h-[85vh]">
        <div className="mx-auto w-full max-w-md p-4 flex flex-col gap-4 overflow-y-auto">
          <DrawerHeader className="px-0 pt-0 text-left">
            <DrawerTitle className="font-heading text-2xl text-text-primary">
              {establishment.name}
            </DrawerTitle>
            <DrawerDescription className="text-text-secondary flex flex-col gap-2 mt-2">
              {establishment.address && (
                <div className="flex items-start gap-2">
                  <MapPin className="w-4 h-4 mt-0.5 shrink-0" />
                  <span>{establishment.address}</span>
                </div>
              )}
              {establishment.phoneNumber && (
                <div className="flex items-center gap-2">
                  <Phone className="w-4 h-4 shrink-0" />
                  <span>{establishment.phoneNumber}</span>
                </div>
              )}
              {establishment.website && (
                <div className="flex items-center gap-2">
                  <Globe className="w-4 h-4 shrink-0" />
                  <a href={establishment.website} target="_blank" rel="noreferrer" className="text-primary-700 hover:underline line-clamp-1">
                    {establishment.website}
                  </a>
                </div>
              )}
            </DrawerDescription>
          </DrawerHeader>

          <Separator className="bg-border" />

          <div className="flex flex-col gap-3">
            <h3 className="font-heading font-semibold text-text-primary flex items-center gap-2">
              <Star className="w-4 h-4 text-accent-600" />
              Avaliações de Acessibilidade
            </h3>
            
            <div className="flex flex-col gap-4 mt-2">
              {establishment.evaluationsSummary?.averages && Object.keys(establishment.evaluationsSummary.averages).length > 0 ? (
                Object.entries(establishment.evaluationsSummary.averages).map(([criterion, avg]) => {
                  const info = CRITERION_MAP[criterion];
                  if (!info) return null;
                  const Icon = info.Icon;
                  return (
                    <div key={criterion} className="flex flex-col gap-1">
                      <div className="flex justify-between items-center">
                        <div className="flex items-center gap-2 text-sm font-medium text-text-primary">
                          <Icon className="w-4 h-4 text-text-secondary" />
                          {info.label}
                        </div>
                        <span className={`text-sm font-bold ${getTextColorClass(avg)}`}>
                          {avg.toFixed(1)}
                        </span>
                      </div>
                      <div className="w-full h-2 rounded-full bg-surface-card border border-border overflow-hidden">
                        <div className={`h-full ${getColorClass(avg)}`} style={{ width: `${avg * 10}%` }} />
                      </div>
                    </div>
                  );
                })
              ) : (
                <p className="text-sm text-text-secondary italic">Nenhuma avaliação disponível.</p>
              )}
            </div>
          </div>

          <DrawerFooter className="px-0 pb-0 pt-4">
            <Button className="w-full bg-accent-600 hover:bg-accent-700 text-white font-medium shadow-md">
              Avaliar este local
            </Button>
          </DrawerFooter>
        </div>
      </DrawerContent>
    </Drawer>
  );
}
