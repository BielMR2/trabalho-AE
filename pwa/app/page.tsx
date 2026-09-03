"use client";

import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { APIProvider } from "@vis.gl/react-google-maps";
import { Plus } from "lucide-react";
import { fetchApi } from "@/utils/dataAccess";
import { ViewportBounds, Establishment } from "@/types/Establishment";
import MapView from "@/components/home/MapView";
import FilterSidebar from "@/components/home/FilterSidebar";
import EstablishmentDrawer from "@/components/home/EstablishmentDrawer";
import { Button } from "@/components/ui/button";

export default function HomePage() {
  const [viewport, setViewport] = useState<ViewportBounds | null>(null);
  const [selectedEstablishment, setSelectedEstablishment] = useState<Establishment | null>(null);
  const [activeFilters, setActiveFilters] = useState<{
    name?: string;
    criterionAverages?: Record<string, string>;
  }>({});

  const { data: establishments = [], isLoading } = useQuery<Establishment[]>({
    queryKey: ["establishments", viewport, activeFilters],
    queryFn: async () => {
      if (!viewport) return [];
      const params = new URLSearchParams();
      params.append("viewport[south]", viewport.south.toString());
      params.append("viewport[west]", viewport.west.toString());
      params.append("viewport[north]", viewport.north.toString());
      params.append("viewport[east]", viewport.east.toString());
      params.append("viewport[zoom]", viewport.zoom.toString());
      
      if (activeFilters.name) {
        params.append("name", activeFilters.name);
      }
      if (activeFilters.criterionAverages) {
        Object.entries(activeFilters.criterionAverages).forEach(([key, value]) => {
          params.append(`criterionAverages[${key}]`, value);
        });
      }

      const response = await fetchApi<any>(`/establishments?${params.toString()}`);
      return response?.data?.["hydra:member"] || [];
    },
    enabled: !!viewport,
  });



  return (
    <APIProvider apiKey={process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY || ""}>
      <div className="flex h-screen w-full bg-surface relative overflow-hidden">
        <FilterSidebar filters={activeFilters} onFiltersChange={setActiveFilters} />
        
        <div className="flex-1 relative">
          <MapView 
            establishments={establishments}
            isLoading={isLoading}
            onMarkerClick={setSelectedEstablishment}
            selectedEstablishment={selectedEstablishment}
            onViewportChange={setViewport}
            showZoomMessage={viewport ? viewport.zoom < 14 : false}
          />
        </div>

        <EstablishmentDrawer 
          establishment={selectedEstablishment} 
          onClose={() => setSelectedEstablishment(null)} 
        />

        <Button 
          className="absolute bottom-6 right-6 h-14 w-14 rounded-full bg-accent-600 hover:bg-accent-700 shadow-lg text-white"
          onClick={() => {
            // handle add evaluation navigation
          }}
        >
          <Plus size={24} />
        </Button>
      </div>
    </APIProvider>
  );
}
