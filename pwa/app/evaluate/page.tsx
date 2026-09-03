// Trigger Next.js hot reload
"use client";

import React, { useEffect, useRef, useState, Suspense } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { useForm, Controller } from "react-hook-form";
import * as yup from "yup";
import { yupResolver } from "@hookform/resolvers/yup";
import { useMutation } from "@tanstack/react-query";
import { APIProvider, useMapsLibrary } from "@vis.gl/react-google-maps";
import { ArrowLeft, Accessibility, Bath, Footprints, BookOpen, Hand, Dog, MapPin } from "lucide-react";

import { useAccessToken } from "@/hooks/useAuth";
import { fetchApi } from "@/utils/dataAccess";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Slider } from "@/components/ui/slider";
import { toast } from "@/components/ui/toast";

declare var google: any;

const CRITERIA = [
  { id: "wheelchair_accessible", label: "Acesso p/ Cadeira de Rodas", icon: Accessibility },
  { id: "accessible_restroom", label: "Banheiros Acessíveis", icon: Bath },
  { id: "tactile_paving", label: "Piso Tátil", icon: Footprints },
  { id: "braille_signage", label: "Sinalização em Braille", icon: BookOpen },
  { id: "sign_language", label: "Atendimento em Libras", icon: Hand },
  { id: "service_animal_allowed", label: "Animais de Serviço", icon: Dog },
];

const schema = yup.object().shape({
  establishmentGooglePlaceId: yup.string().required("Por favor, selecione um local válido."),
  establishmentName: yup.string(),
  comment: yup.string().max(500, "O comentário não pode ter mais de 500 caracteres"),
  ratings: yup
    .object()
    .test(
      "at-least-one-rating",
      "É necessário avaliar pelo menos um critério.",
      (value) => {
        if (!value) return false;
        return Object.values(value).some((val) => val !== undefined && val !== null);
      }
    )
    .required("É necessário avaliar pelo menos um critério."),
});

type FormData = yup.InferType<typeof schema>;

function PlaceAutocomplete({ 
  onPlaceSelect 
}: { 
  onPlaceSelect: (placeId: string, name: string) => void 
}) {
  const inputRef = useRef<HTMLInputElement>(null);
  const places = useMapsLibrary("places");
  const [autocomplete, setAutocomplete] = useState<any | null>(null);

  useEffect(() => {
    if (!places || !inputRef.current) return;

    const widget = new places.Autocomplete(inputRef.current, {
      fields: ["place_id", "name"],
    });

    setAutocomplete(widget);
  }, [places]);

  useEffect(() => {
    if (!autocomplete) return;

    const listener = autocomplete.addListener("place_changed", () => {
      const place = autocomplete.getPlace();
      if (place.place_id && place.name) {
        onPlaceSelect(place.place_id, place.name);
      }
    });

    return () => {
      google.maps.event.removeListener(listener);
    };
  }, [autocomplete, onPlaceSelect]);

  return (
    <Input 
      ref={inputRef} 
      placeholder="Pesquisar por um local..." 
      className="w-full"
    />
  );
}

function EvaluationForm() {
  const router = useRouter();
  const searchParams = useSearchParams();

  
  const { accessToken, session, isPending } = useAccessToken();

  const placeIdParam = searchParams.get("placeId");
  const placeNameParam = searchParams.get("name");

  useEffect(() => {
    if (!isPending && !session) {
      router.push(`/login?callbackUrl=${encodeURIComponent("/evaluate")}`);
    }
  }, [isPending, session, router]);

  const {
    control,
    register,
    handleSubmit,
    setValue,
    watch,
    setError,
    formState: { errors, isValid, isSubmitting },
  } = useForm<FormData>({
    resolver: yupResolver(schema),
    mode: "onChange",
    defaultValues: {
      establishmentGooglePlaceId: placeIdParam || "",
      establishmentName: placeNameParam || "",
      comment: "",
      ratings: {},
    },
  });

  const selectedPlaceId = watch("establishmentGooglePlaceId");
  const selectedPlaceName = watch("establishmentName");
  const commentValue = watch("comment");

  const submitMutation = useMutation({
    mutationFn: async (data: FormData) => {
      if (!accessToken) throw new Error("Não autenticado");

      const ratingsPayload = Object.entries(data.ratings || {})
        .filter(([_, rating]) => rating !== undefined && rating !== null)
        .map(([criterionId, rating]) => ({
          criterion: `/criterion_enums/${criterionId}`,
          rating: rating as number,
        }));

      const payload = {
        establishmentGooglePlaceId: data.establishmentGooglePlaceId,
        comment: data.comment || undefined,
        ratings: ratingsPayload,
      };

      const res = await fetchApi("/evaluations", {
        method: "POST",
        body: JSON.stringify(payload),
      }, accessToken);

      return res;
    },
    onSuccess: () => {
      toast.add({
        title: "Avaliação enviada!",
        description: "Obrigado por contribuir com nossa comunidade.",

      });
      router.push("/");
    },
    onError: (error: any) => {
      if (error.fields && Object.keys(error.fields).length > 0) {
        Object.entries(error.fields).forEach(([propertyPath, message]) => {
          if (propertyPath === "comment") {
            setError("comment", { message: message as string });
          } else {
            toast.add({
              title: "Erro de validação",
              description: message as string,

            });
          }
        });
      } else {
        toast.add({
          title: "Erro",
          description: error.message || "Ocorreu um erro ao enviar a avaliação.",

        });
      }
    },
  });

  const onSubmit = (data: FormData) => {
    submitMutation.mutate(data);
  };

  const getRatingColor = (val: number | undefined | null) => {
    if (val === undefined || val === null) return "text-muted-foreground";
    if (val >= 7) return "text-success";
    if (val >= 5) return "text-warning";
    return "text-danger";
  };

  if (isPending || (!session && !isPending)) {
    return (
      <div className="flex h-screen items-center justify-center">
        <p className="text-secondary text-lg">Carregando...</p>
      </div>
    );
  }

  return (
    <div className="max-w-2xl mx-auto p-4 sm:p-6 pb-20">
      <header className="flex items-center mb-6">
        <Button variant="ghost" size="icon" onClick={() => router.back()} className="mr-2">
          <ArrowLeft className="w-5 h-5" />
        </Button>
        <h1 className="text-2xl font-heading font-bold text-primary">Nova Avaliação</h1>
      </header>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-8">
        <section>
          <h2 className="text-lg font-semibold mb-3 text-primary">1. Selecione o Local</h2>
          {selectedPlaceId && selectedPlaceName ? (
            <Card className="bg-surface-card border border-border">
              <CardContent className="flex items-center justify-between p-4">
                <div className="flex items-center gap-3">
                  <div className="bg-primary-900/10 p-2 rounded-full">
                    <MapPin className="w-5 h-5 text-primary-700" />
                  </div>
                  <div>
                    <p className="font-semibold text-text-primary">{selectedPlaceName}</p>
                    <p className="text-sm text-text-secondary truncate">ID: {selectedPlaceId.substring(0, 10)}...</p>
                  </div>
                </div>
                <Button 
                  variant="outline" 
                  size="sm" 
                  onClick={() => {
                    setValue("establishmentGooglePlaceId", "");
                    setValue("establishmentName", "");
                  }}
                >
                  Alterar
                </Button>
              </CardContent>
            </Card>
          ) : (
            <div>
              <PlaceAutocomplete 
                onPlaceSelect={(id, name) => {
                  setValue("establishmentGooglePlaceId", id, { shouldValidate: true });
                  setValue("establishmentName", name);
                }} 
              />
              {errors.establishmentGooglePlaceId && (
                <p className="text-danger text-sm mt-1">{errors.establishmentGooglePlaceId.message}</p>
              )}
            </div>
          )}
        </section>

        <section>
          <div className="mb-3">
            <h2 className="text-lg font-semibold text-primary">2. Avalie os Critérios</h2>
            <p className="text-sm text-text-secondary">Arraste para avaliar os critérios que você observou (0 a 10). Pelo menos um é obrigatório.</p>
          </div>
          
          <div className="grid gap-4 sm:grid-cols-2">
            {CRITERIA.map((criterion) => {
              const Icon = criterion.icon;
              return (
                <Card key={criterion.id} className="bg-surface-card border border-border">
                  <CardHeader className="pb-2 p-4">
                    <CardTitle className="text-md flex items-center gap-2">
                      <Icon className="w-4 h-4 text-accent-600" />
                      {criterion.label}
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="p-4 pt-0">
                    <Controller
                      control={control}
                      name={`ratings.${criterion.id}` as any}
                      render={({ field: { value, onChange } }) => (
                        <div className="space-y-3">
                          <div className="flex justify-between items-center text-sm font-medium">
                            <span className={getRatingColor(value)}>
                              {value !== undefined && value !== null ? value : "Não avaliado"}
                            </span>
                            {(value !== undefined && value !== null) && (
                              <button
                                type="button"
                                onClick={() => onChange(undefined)}
                                className="text-xs text-text-secondary hover:text-text-primary underline"
                              >
                                Limpar
                              </button>
                            )}
                          </div>
                          <Slider
                            defaultValue={[5]}
                            value={value !== undefined && value !== null ? [value] : [5]}
                            min={0}
                            max={10}
                            step={1}
                            onValueChange={(vals) => onChange(Array.isArray(vals) ? vals[0] : vals)}
                            className={value === undefined || value === null ? "opacity-50" : ""}
                          />
                        </div>
                      )}
                    />
                  </CardContent>
                </Card>
              );
            })}
          </div>
          {errors.ratings && (
            <p className="text-danger text-sm mt-2">{errors.ratings.message as string}</p>
          )}
        </section>

        <section>
          <div className="mb-3">
            <h2 className="text-lg font-semibold text-primary">3. Comentários (Opcional)</h2>
            <p className="text-sm text-text-secondary">Descreva sua experiência detalhadamente.</p>
          </div>
          <Textarea
            placeholder="Como foi sua experiência de acessibilidade?"
            className="w-full min-h-[100px]"
            {...register("comment")}
          />
          <div className="flex justify-between mt-1 text-sm">
            {errors.comment ? (
              <span className="text-danger">{errors.comment.message}</span>
            ) : (
              <span></span>
            )}
            <span className={`text-text-secondary ${commentValue && commentValue.length > 500 ? 'text-danger' : ''}`}>
              {commentValue?.length || 0} / 500
            </span>
          </div>
        </section>

        <Button 
          type="submit" 
          className="w-full py-6 text-lg" 
          disabled={!isValid || submitMutation.isPending}
        >
          {submitMutation.isPending ? "Enviando..." : "Enviar Avaliação"}
        </Button>
      </form>
    </div>
  );
}

export default function EvaluatePage() {
  const apiKey = process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY || "";
  
  return (
    <APIProvider apiKey={apiKey}>
      <Suspense fallback={<div className="flex h-screen items-center justify-center">Carregando...</div>}>
        <EvaluationForm />
      </Suspense>
    </APIProvider>
  );
}
