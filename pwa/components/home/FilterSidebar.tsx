"use client";

import { Input } from "../ui/input";
import { Button } from "../ui/button";
import { Plus, User } from "lucide-react";
import { useSession, signInWithKeycloak, signOutWithKeycloak } from "../../hooks/useAuth";
import { useRouter } from "next/navigation";
export interface Filters {
  name: string;
  address: string;
  criterion_average: Record<string, string>;
}

interface FilterSidebarProps {
  filters: Filters;
  setFilters: React.Dispatch<React.SetStateAction<Filters>>;
}

export function FilterSidebar({ filters, setFilters }: FilterSidebarProps) {
  const router = useRouter();
  const { session, isPending } = useSession();

  const handleAddEvaluation = async () => {
    if (session) {
      router.push("/evaluation");
    } else {
      await signInWithKeycloak(window.location.origin + "/evaluation");
    }
  };

  const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFilters((prev) => ({ ...prev, name: e.target.value }));
  };

  const handleAddressChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFilters((prev) => ({ ...prev, address: e.target.value }));
  };

  const handleCriterionChange = (criterion: string, value: string) => {
    setFilters((prev) => {
      const newCriterionAverage = { ...prev.criterion_average };
      if (value === "") {
        delete newCriterionAverage[criterion];
      } else {
        newCriterionAverage[criterion] = value;
      }
      return { ...prev, criterion_average: newCriterionAverage };
    });
  };

  const criteriaList = [
    { id: "wheelchair_accessible", label: "Acesso Físico" },
    { id: "accessible_restroom", label: "Banheiro Adaptado" },
    { id: "tactile_paving", label: "Piso Tátil" },
    { id: "braille_signage", label: "Sinalização em Braille" },
    { id: "sign_language", label: "Atendimento em Libras" },
    { id: "service_animal_allowed", label: "Animais de Serviço" },
  ];

  return (
    <aside className="w-80 border-r bg-background p-6 flex flex-col gap-6 overflow-y-auto h-full">
      <div>
        <h2 className="text-xl font-semibold tracking-tight mb-4">Filtros</h2>
      </div>

      <div className="space-y-4">
        <div className="space-y-2">
          <label htmlFor="name" className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            Nome do local
          </label>
          <Input
            id="name"
            placeholder="Buscar por nome..."
            value={filters.name}
            onChange={handleNameChange}
          />
        </div>

        <div className="space-y-2">
          <label htmlFor="address" className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            Endereço
          </label>
          <Input
            id="address"
            placeholder="Buscar por endereço..."
            value={filters.address}
            onChange={handleAddressChange}
          />
        </div>
      </div>

      <div className="space-y-4 flex-1">
        <h3 className="text-sm font-semibold tracking-tight text-muted-foreground uppercase">Avaliações</h3>
        {criteriaList.map((criterion) => (
          <div key={criterion.id} className="space-y-2">
            <label className="text-sm font-medium leading-none">{criterion.label}</label>
            <select
              className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
              value={filters.criterion_average[criterion.id] || ""}
              onChange={(e) => handleCriterionChange(criterion.id, e.target.value)}
            >
              <option value="">Qualquer nota</option>
              <option value="bom">Bom (≥ 7.0)</option>
              <option value="medio">Médio (5.0 a 6.9)</option>
              <option value="ruim">Ruim (&lt; 5.0)</option>
            </select>
          </div>
        ))}
      </div>

      <div className="mt-auto pt-6 border-t flex flex-col gap-3">
        <Button onClick={handleAddEvaluation} className="w-full gap-2" variant="default">
          <Plus className="h-4 w-4" />
          Adicionar Avaliação
        </Button>

        {isPending ? (
          <Button variant="outline" disabled className="w-full">Carregando...</Button>
        ) : session?.user ? (
          <div className="flex flex-col gap-2">
            <div className="text-sm text-center">
              Logado como <span className="font-semibold">{session.user.name || session.user.email}</span>
            </div>
            <Button variant="outline" onClick={() => signOutWithKeycloak()} className="w-full">
              Sair
            </Button>
          </div>
        ) : (
          <Button variant="outline" onClick={() => signInWithKeycloak()} className="w-full">
            <User className="h-4 w-4 mr-2" />
            Entrar
          </Button>
        )}
      </div>
    </aside>
  );
}

