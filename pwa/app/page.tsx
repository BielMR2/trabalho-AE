"use client";

import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { fetchApi } from "@/utils/dataAccess";
import { Establishment } from "@/types/Establishment";
import FilterSidebar, { ActiveFilters } from "@/components/home/FilterSidebar";
import MapView from "@/components/home/MapView";
import EstablishmentDrawer from "@/components/home/EstablishmentDrawer";
import { useMercure } from "@/utils/mercure";

const INITIAL_FILTERS: ActiveFilters = {
  name: "",
  address: "",
  criterionAverages: {},
};

import { APIProvider } from "@vis.gl/react-google-maps";
import { signInWithKeycloak, useSession } from "@/hooks/useAuth";
import { useRouter } from "next/navigation";
import { Plus } from "lucide-react";
import { Button } from "@/components/ui/button";

function buildEstablishmentsUrl(filters: ActiveFilters): string {
  const params = new URLSearchParams({ page: "1" });

  if (filters.name) {
    params.set("name", filters.name);
  }
  if (filters.address) {
    params.set("address", filters.address);
  }
  for (const [criterion, status] of Object.entries(filters.criterionAverages)) {
    if (status) {
      params.set(`criterion_average[${criterion}]`, status);
    }
  }

  return `/establishments?${params.toString()}`;
}

export default function HomePage() {
  const [activeFilters, setActiveFilters] = useState<ActiveFilters>(INITIAL_FILTERS);
  const [selectedEstablishment, setSelectedEstablishment] = useState<Establishment | null>(null);
  
  const { data: session, isPending } = useSession();
  const router = useRouter();

  const handleAddReview = async () => {
    if (isPending) return;
    const evaluateUrl = `/evaluate`;
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

  const { data: response, isLoading } = useQuery({
    queryKey: ["establishments", activeFilters],
    queryFn: async () => {
      const url = buildEstablishmentsUrl(activeFilters);
      return await fetchApi<any>(url);
    },
  });

  const mercureData = useMercure(response?.data, response?.hubURL);
  const establishments = (mercureData?.["member"] ?? mercureData?.["hydra:member"] ?? []) as Establishment[];

  return (
    <APIProvider apiKey={process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY || ""}>
      <div className="flex h-[calc(100vh-53px)] overflow-hidden">
        <FilterSidebar filters={activeFilters} onApplyFilters={setActiveFilters} onSelectEstablishment={setSelectedEstablishment} />
        <div className="flex-1 relative">
          <MapView
            establishments={establishments}
            isLoading={isLoading}
            onMarkerClick={setSelectedEstablishment}
            selectedEstablishment={selectedEstablishment}
          />
          <Button
            onClick={handleAddReview}
            disabled={isPending}
            className="absolute bottom-6 right-6 z-50 rounded-full shadow-xl bg-cyan-700 hover:bg-cyan-800 text-white flex items-center gap-2 px-4 py-6"
          >
            <Plus className="w-5 h-5" />
            <span className="hidden md:inline font-semibold">Adicionar Avaliação</span>
          </Button>
        </div>
        <EstablishmentDrawer
          establishment={selectedEstablishment}
          onClose={() => setSelectedEstablishment(null)}
        />
      </div>
    </APIProvider>
  );
}
