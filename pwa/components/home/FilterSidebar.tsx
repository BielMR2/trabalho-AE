"use client";

import { useState } from "react";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from "@/components/ui/sheet";
import { Filter, X } from "lucide-react";

const CRITERIA = [
  { value: "wheelchair_accessible", label: "Acesso p/ Cadeira de Rodas" },
  { value: "accessible_restroom", label: "Banheiros Acessíveis" },
  { value: "tactile_paving", label: "Piso Tátil" },
  { value: "braille_signage", label: "Sinalização em Braille" },
  { value: "sign_language", label: "Atendimento em Libras" },
  { value: "service_animal_allowed", label: "Animais de Serviço Permitidos" },
] as const;

const STATUS_OPTIONS = [
  { value: "bom", label: "Bom", description: "≥ 7", color: "bg-green-100 text-green-800 border-green-300" },
  { value: "medio", label: "Médio", description: "5 a 7", color: "bg-yellow-100 text-yellow-800 border-yellow-300" },
  { value: "ruim", label: "Ruim", description: "< 5", color: "bg-red-100 text-red-800 border-red-300" },
] as const;

export interface ActiveFilters {
  name: string;
  address: string;
  criterionAverages: Record<string, string>; // { criterion_name: "bom"|"medio"|"ruim" }
}

interface FilterSidebarProps {
  filters: ActiveFilters;
  onApplyFilters: (filters: ActiveFilters) => void;
}

function FilterContent({ filters, onApplyFilters }: FilterSidebarProps) {
  const [name, setName] = useState(filters.name);
  const [address, setAddress] = useState(filters.address);
  const [criterionAverages, setCriterionAverages] = useState<Record<string, string>>(
    { ...filters.criterionAverages }
  );

  const handleCriterionToggle = (criterion: string, status: string) => {
    setCriterionAverages((prev) => {
      const next = { ...prev };
      if (next[criterion] === status) {
        delete next[criterion];
      } else {
        next[criterion] = status;
      }
      return next;
    });
  };

  const handleApply = () => {
    onApplyFilters({ name, address, criterionAverages });
  };

  const handleClear = () => {
    setName("");
    setAddress("");
    setCriterionAverages({});
    onApplyFilters({ name: "", address: "", criterionAverages: {} });
  };

  const hasActiveFilters = name || address || Object.keys(criterionAverages).length > 0;

  return (
    <div className="flex flex-col gap-5 p-4 h-full overflow-y-auto">
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-bold text-gray-900">Filtros</h2>
        {hasActiveFilters && (
          <button
            onClick={handleClear}
            className="text-sm text-cyan-700 hover:text-cyan-900 flex items-center gap-1"
          >
            <X className="w-3 h-3" />
            Limpar
          </button>
        )}
      </div>

      <Separator />

      {/* Name Filter */}
      <div>
        <label className="text-sm font-semibold text-gray-700 mb-1.5 block">Nome</label>
        <Input
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="Ex: Farmácia, Shopping..."
          className="text-sm"
        />
      </div>

      <Separator />

      {/* Address Filter */}
      <div>
        <label className="text-sm font-semibold text-gray-700 mb-1.5 block">Endereço</label>
        <Input
          value={address}
          onChange={(e) => setAddress(e.target.value)}
          placeholder="Rua, bairro, cidade..."
          className="text-sm"
        />
      </div>

      <Separator />

      {/* Criterion Average Filters */}
      <div>
        <label className="text-sm font-semibold text-gray-700 mb-3 block">
          Acessibilidade
        </label>
        <div className="flex flex-col gap-4">
          {CRITERIA.map((criterion) => (
            <div key={criterion.value}>
              <p className="text-xs font-medium text-gray-600 mb-1.5">{criterion.label}</p>
              <div className="flex gap-1.5">
                {STATUS_OPTIONS.map((status) => {
                  const isActive = criterionAverages[criterion.value] === status.value;
                  return (
                    <button
                      key={status.value}
                      onClick={() => handleCriterionToggle(criterion.value, status.value)}
                      className={`
                        flex-1 text-xs py-1.5 px-2 rounded-md border transition-all font-medium
                        ${isActive
                          ? status.color + " border-current shadow-sm"
                          : "bg-gray-50 text-gray-500 border-gray-200 hover:bg-gray-100"
                        }
                      `}
                      title={status.description}
                    >
                      {status.label}
                    </button>
                  );
                })}
              </div>
            </div>
          ))}
        </div>
      </div>

      <Separator />

      {/* Apply Button */}
      <Button onClick={handleApply} className="w-full bg-cyan-700 hover:bg-cyan-800 text-white">
        Aplicar Filtros
      </Button>
    </div>
  );
}

export default function FilterSidebar({ filters, onApplyFilters }: FilterSidebarProps) {
  const [sheetOpen, setSheetOpen] = useState(false);

  const handleApplyMobile = (newFilters: ActiveFilters) => {
    onApplyFilters(newFilters);
    setSheetOpen(false);
  };

  return (
    <>
      {/* Desktop: fixed sidebar */}
      <aside className="hidden md:block w-72 flex-shrink-0 border-r border-gray-200 bg-white overflow-y-auto">
        <FilterContent filters={filters} onApplyFilters={onApplyFilters} />
      </aside>

      {/* Mobile: floating button + Sheet */}
      <div className="md:hidden absolute top-4 left-4 z-20">
        <Sheet open={sheetOpen} onOpenChange={setSheetOpen}>
          <SheetTrigger render={<Button variant="secondary" className="shadow-lg" />}>
              <Filter className="w-4 h-4 mr-2" />
              Filtros
          </SheetTrigger>
          <SheetContent side="left" className="w-[85vw] sm:w-[400px] p-0">
            <SheetHeader className="p-4 pb-0">
              <SheetTitle>Filtrar Locais</SheetTitle>
            </SheetHeader>
            <FilterContent filters={filters} onApplyFilters={handleApplyMobile} />
          </SheetContent>
        </Sheet>
      </div>
    </>
  );
}

