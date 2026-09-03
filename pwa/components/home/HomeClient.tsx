"use client";

import { useState, useEffect, useRef } from "react";
import { useQuery } from "@tanstack/react-query";
import { FilterSidebar, Filters } from "./FilterSidebar";
import { MapView } from "./MapView";
import { EstablishmentDrawer } from "./EstablishmentDrawer";
import { fetchApi, buildUriFromFilters } from "../../utils/dataAccess";
import { Establishment, ViewportBounds } from "../../types/Establishment";
import { useMercure } from "../../utils/mercure";
import { PagedCollection } from "../../types/collection";

export function HomeClient() {
  const [filters, setFilters] = useState<Filters>({
    name: "",
    address: "",
    criterion_average: {},
  });
  
  const [viewportBounds, setViewportBounds] = useState<ViewportBounds | null>(null);
  const [selectedEstablishmentId, setSelectedEstablishmentId] = useState<string | null>(null);

  const hasValidZoom = viewportBounds !== null && viewportBounds.zoom >= 13;

  const { data: fetchResponse } = useQuery({
    queryKey: ["establishments", filters, viewportBounds],
    queryFn: async ({ queryKey }) => {
      const [_key, currentFilters, currentViewport] = queryKey as [string, Filters, ViewportBounds | null];
      
      const allFilters: Record<string, any> = { ...currentFilters };
      
      if (currentViewport && currentViewport.zoom >= 13) {
        allFilters.viewport = currentViewport;
      }
      
      const endpoint = buildUriFromFilters("/establishments", allFilters);
      
      return await fetchApi<PagedCollection<Establishment>>(endpoint);
    },
    enabled: hasValidZoom,
  });

  // Use mercure for realtime updates if available
  const collection = useMercure(fetchResponse?.data, fetchResponse?.hubURL) as any;
  const currentEstablishments: Establishment[] = collection?.["hydra:member"] || collection?.member || [];

  const [cachedEstablishments, setCachedEstablishments] = useState<Map<string, { data: Establishment, zoom: number }>>(new Map());
  const prevFiltersRef = useRef(filters);

  useEffect(() => {
    // If filters change, clear the accumulated cache
    if (JSON.stringify(prevFiltersRef.current) !== JSON.stringify(filters)) {
      setCachedEstablishments(new Map());
      prevFiltersRef.current = filters;
    }
  }, [filters]);

  useEffect(() => {
    if (currentEstablishments.length > 0 && viewportBounds?.zoom) {
      const currentZoom = viewportBounds.zoom;
      
      setCachedEstablishments((prev) => {
        const next = new Map(prev);
        let changed = false;
        
        currentEstablishments.forEach((est) => {
          const id = est["@id"] || est.id;
          if (!id) return;
          
          const existing = next.get(id);
          // Update if it doesn't exist, if it changed (Mercure updates), or if the zoom changed
          if (
            !existing || 
            existing.zoom !== currentZoom ||
            JSON.stringify(existing.data) !== JSON.stringify(est)
          ) {
            next.set(id, { data: est, zoom: currentZoom });
            changed = true;
          }
        });
        
        return changed ? next : prev;
      });
    }
  }, [currentEstablishments, viewportBounds?.zoom]);

  const establishmentsToRender = Array.from(cachedEstablishments.values())
    .filter((cached) => cached.zoom === viewportBounds?.zoom)
    .map((cached) => cached.data);

  return (
    <div className="flex h-screen w-full overflow-hidden">
      <FilterSidebar filters={filters} setFilters={setFilters} />
      
      <main className="flex-1 relative">
        <MapView 
          establishments={establishmentsToRender} 
          onMarkerClick={(est) => setSelectedEstablishmentId(est["@id"] as string)}
          onBoundsChange={setViewportBounds}
        />
      </main>

      <EstablishmentDrawer 
        establishmentId={selectedEstablishmentId} 
        onClose={() => setSelectedEstablishmentId(null)} 
      />
    </div>
  );
}
