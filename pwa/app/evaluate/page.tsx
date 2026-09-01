"use client";

import { useState, useRef, useEffect, Suspense } from "react";
import { useSession } from "@/hooks/useAuth";
import { useRouter, useSearchParams } from "next/navigation";
import { APIProvider, useMapsLibrary } from "@vis.gl/react-google-maps";
import { fetchApi } from "@/utils/dataAccess";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Slider } from "@/components/ui/slider";

const CRITERIA = [
  { value: "wheelchair_accessible", label: "Acesso p/ Cadeira de Rodas" },
  { value: "accessible_restroom", label: "Banheiros Acessíveis" },
  { value: "tactile_paving", label: "Piso Tátil" },
  { value: "braille_signage", label: "Sinalização em Braille" },
  { value: "sign_language", label: "Atendimento em Libras" },
  { value: "service_animal_allowed", label: "Animais de Serviço Permitidos" },
];

function EvaluateForm() {
  const { data: session } = useSession();
  const router = useRouter();
  const searchParams = useSearchParams();
  
  const initialPlaceId = searchParams.get("placeId") || "";
  const initialName = searchParams.get("name") || "";
  
  const [googlePlaceId, setGooglePlaceId] = useState<string>(initialPlaceId);
  const [address, setAddress] = useState<string>(initialName);
  const [comment, setComment] = useState("");
  const [ratings, setRatings] = useState<Record<string, number>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  const placesLibrary = useMapsLibrary("places");
  const addressInputRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    // If we already have a placeId from URL, we don't strictly need the autocomplete,
    // but we can still initialize it for users who want to change the location.
    if (!placesLibrary || !addressInputRef.current) return;
    addressInputRef.current.innerHTML = "";
    
    // @ts-ignore
    const autocomplete = new placesLibrary.PlaceAutocompleteElement();
    
    autocomplete.addEventListener("gmp-placeselect", (e: any) => {
      const place = e.place;
      if (!place) return;
      
      const placeId = place.id || (place.name ? place.name.replace("places/", "") : null) || place.place_id;
      console.log("Selected place:", place);
      if (placeId) {
        setGooglePlaceId(placeId);
        setAddress(place.displayName || "");
      }
    });
    
    addressInputRef.current.appendChild(autocomplete);
  }, [placesLibrary]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!session) {
      alert("Você precisa estar logado!");
      return;
    }
    if (!googlePlaceId) {
      alert("Selecione um local válido do Google Maps.");
      return;
    }

    const ratingsPayload = Object.entries(ratings).map(([criterion, rating]) => ({
      criterion: `/criterion_enums/${criterion}`,
      rating,
    }));

    if (ratingsPayload.length === 0) {
      alert("Avalie pelo menos um critério.");
      return;
    }

    setIsSubmitting(true);
    try {
      await fetchApi("/evaluations", {
        method: "POST",
        body: JSON.stringify({
          establishmentGooglePlaceId: googlePlaceId,
          comment: comment || null,
          ratings: ratingsPayload,
        }),
      });
      alert("Avaliação enviada com sucesso!");
      router.push("/");
    } catch (error: any) {
      alert("Erro ao enviar avaliação: " + (error.message || "Tente novamente."));
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleRatingChange = (criterion: string, val: number) => {
    setRatings(prev => ({ ...prev, [criterion]: val }));
  };

  return (
    <div className="max-w-2xl mx-auto p-4 md:p-6 bg-white rounded-lg shadow-sm border mt-8">
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Nova Avaliação</h1>
      
      <form onSubmit={handleSubmit} className="space-y-6">
        <div>
          <label className="block text-sm font-semibold text-gray-700 mb-1.5">
            Localização (Busque no Google Maps)
          </label>
          <div ref={addressInputRef} className="w-full min-h-[40px] border rounded-md"></div>
          {address && <p className="text-sm text-green-600 mt-1">Local selecionado: {address}</p>}
        </div>

        <div>
          <label className="block text-sm font-semibold text-gray-700 mb-3">
            Critérios de Acessibilidade (0 a 10)
          </label>
          <div className="space-y-4 bg-gray-50 p-4 rounded-lg border">
            {CRITERIA.map((criterion) => {
              const currentVal = ratings[criterion.value];
              return (
                <div key={criterion.value} className="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                  <span className="text-sm font-medium text-gray-700 flex-1">
                    {criterion.label}
                  </span>
                  <div className="flex items-center gap-3 w-full sm:w-1/2">
                    <input
                      type="range"
                      min="0"
                      max="10"
                      step="1"
                      value={currentVal !== undefined ? currentVal : 0}
                      onChange={(e) => handleRatingChange(criterion.value, parseInt(e.target.value, 10))}
                      className={`w-full accent-cyan-700 ${currentVal === undefined ? 'opacity-50 grayscale' : ''}`}
                    />
                    <span className="text-sm font-bold w-6 text-center">
                      {currentVal !== undefined ? currentVal : "-"}
                    </span>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        <div>
          <label className="block text-sm font-semibold text-gray-700 mb-1.5">
            Comentário (opcional)
          </label>
          <Textarea
            value={comment}
            onChange={(e) => setComment(e.target.value)}
            placeholder="Descreva a acessibilidade do local..."
            className="h-24"
          />
        </div>

        <div className="pt-2 flex justify-end gap-3">
          <Button type="button" variant="outline" onClick={() => router.push("/")}>
            Cancelar
          </Button>
          <Button type="submit" disabled={isSubmitting} className="bg-cyan-700 hover:bg-cyan-800 text-white">
            {isSubmitting ? "Enviando..." : "Enviar Avaliação"}
          </Button>
        </div>
      </form>
    </div>
  );
}

export default function EvaluatePage() {
  return (
    <APIProvider apiKey={process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY || ""}>
      <div className="min-h-[calc(100vh-53px)] bg-gray-50 p-4">
        <Suspense fallback={<div className="text-center p-8">Carregando...</div>}>
          <EvaluateForm />
        </Suspense>
      </div>
    </APIProvider>
  );
}

