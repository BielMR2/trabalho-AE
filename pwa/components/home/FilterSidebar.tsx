"use client";

import { useState, useEffect } from "react";
import { Filter, Search } from "lucide-react";
import { Sheet, SheetContent, SheetTrigger } from "@/components/ui/sheet";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";

interface FilterSidebarProps {
  filters: {
    name?: string;
    criterionAverages?: Record<string, string>;
  };
  onFiltersChange: (filters: { name?: string; criterionAverages?: Record<string, string> }) => void;
}

export default function FilterSidebar({ filters, onFiltersChange }: FilterSidebarProps) {
  const [searchTerm, setSearchTerm] = useState(filters.name || "");
  const [criteria, setCriteria] = useState<Record<string, string>>(filters.criterionAverages || {});

  useEffect(() => {
    const timer = setTimeout(() => {
      onFiltersChange({ name: searchTerm, criterionAverages: criteria });
    }, 300);
    return () => clearTimeout(timer);
  }, [searchTerm, criteria, onFiltersChange]);

  const toggleCriterion = (criterion: string, level: string) => {
    setCriteria(prev => {
      const newCriteria = { ...prev };
      if (newCriteria[criterion] === level) {
        delete newCriteria[criterion];
      } else {
        newCriteria[criterion] = level;
      }
      return newCriteria;
    });
  };

  const renderFilterOptions = () => (
    <div className="flex flex-col gap-6 w-full mt-8">
      <div className="space-y-2">
        <label className="text-sm font-medium text-text-secondary font-heading">Buscar</label>
        <div className="relative">
          <Search className="absolute left-3 top-2.5 text-text-secondary w-4 h-4" />
          <Input 
            className="pl-9 bg-surface-card border-border" 
            placeholder="Nome do local..." 
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>
      </div>

      <div className="space-y-4">
        <label className="text-sm font-medium text-text-secondary font-heading">Filtros de Acessibilidade</label>
        
        {['wheelchair_accessible', 'accessible_restroom', 'tactile_paving', 'braille_signage', 'sign_language', 'service_animal_allowed'].map(criterion => {
            const labels: Record<string, string> = {
                wheelchair_accessible: 'Cadeira de Rodas',
                accessible_restroom: 'Banheiro Acessível',
                tactile_paving: 'Piso Tátil',
                braille_signage: 'Sinalização Braille',
                sign_language: 'Libras',
                service_animal_allowed: 'Animais de Serviço',
            };
            
            return (
              <div key={criterion} className="space-y-2">
                <span className="text-xs text-text-primary capitalize">{labels[criterion]}</span>
                <div className="flex gap-2">
                  {['bom', 'medio', 'ruim'].map(level => (
                    <Button
                      key={level}
                      variant={criteria[criterion] === level ? "default" : "outline"}
                      size="sm"
                      onClick={() => toggleCriterion(criterion, level)}
                      className={`flex-1 text-xs capitalize ${criteria[criterion] === level ? 'bg-primary-700 text-white hover:bg-primary-900' : 'text-text-secondary hover:text-text-primary'}`}
                    >
                      {level}
                    </Button>
                  ))}
                </div>
              </div>
            );
        })}
      </div>
    </div>
  );

  return (
    <>
      <div className="hidden md:flex flex-col w-[280px] h-full bg-surface-card border-r border-border p-4 shadow-sm z-10">
        <div className="flex items-center gap-2 mb-4">
          <Filter className="text-primary-700" />
          <h2 className="text-lg font-heading font-semibold text-text-primary">Filtros</h2>
        </div>
        {renderFilterOptions()}
      </div>

      <div className="md:hidden absolute top-4 left-4 z-10">
        <Sheet>
          <SheetTrigger asChild>
            <Button variant="outline" className="bg-surface-card shadow-sm w-10 h-10 p-0 rounded-full border-border">
              <Filter className="w-5 h-5 text-primary-700" />
            </Button>
          </SheetTrigger>
          <SheetContent side="left" className="w-[85vw] sm:w-[350px] bg-surface p-4">
            <h2 className="text-lg font-heading font-semibold text-text-primary mb-4">Filtros</h2>
            {renderFilterOptions()}
          </SheetContent>
        </Sheet>
      </div>
    </>
  );
}
