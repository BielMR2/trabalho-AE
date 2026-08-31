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

function buildEstablishmentsUrl(filters: ActiveFilters): string {
  let url = "/establishments?page=1";

  if (filters.name) {
    url += `&name=${encodeURIComponent(filters.name)}`;
  }
  if (filters.address) {
    url += `&address=${encodeURIComponent(filters.address)}`;
  }
  for (const [criterion, status] of Object.entries(filters.criterionAverages)) {
    if (status) {
      url += `&criterion_average[${encodeURIComponent(criterion)}]=${encodeURIComponent(status)}`;
    }
  }

  return url;
}

export default function HomePage() {
  const [activeFilters, setActiveFilters] = useState<ActiveFilters>(INITIAL_FILTERS);
  const [selectedEstablishment, setSelectedEstablishment] = useState<Establishment | null>(null);

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
    <div className="flex h-[calc(100vh-53px)] overflow-hidden">
      <FilterSidebar filters={activeFilters} onApplyFilters={setActiveFilters} />
      <div className="flex-1 relative">
        <MapView
          establishments={establishments}
          isLoading={isLoading}
          onMarkerClick={setSelectedEstablishment}
        />
      </div>
      <EstablishmentDrawer
        establishment={selectedEstablishment}
        onClose={() => setSelectedEstablishment(null)}
      />
    </div>
  );
}
