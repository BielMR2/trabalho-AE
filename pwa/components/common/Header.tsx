"use client";

import Link from "next/link";
import { User, LogOut } from "lucide-react";
import { Button } from "@/components/ui/button";

import { useSession, signInWithKeycloak, signOutWithKeycloak } from "../../hooks/useAuth";

export const Header = () => {
  const { data: session, isPending } = useSession();

  return (
    <header className="bg-surface-card border-b border-border sticky top-0 z-30">
      <nav className="flex items-center justify-between px-6 py-3" aria-label="Navegação principal">
        <Link href="/" className="text-xl font-heading font-bold text-primary-900 hover:text-primary-700 transition-colors">
          Acessibiliza
        </Link>
        <div className="flex items-center gap-3">
          {!isPending && session ? (
            <Button
              variant="ghost"
              size="sm"
              onClick={() => signOutWithKeycloak(`${window.location.origin}/`)}
              className="text-text-secondary hover:text-text-primary gap-1.5"
            >
              <LogOut className="w-4 h-4" />
              <span className="hidden sm:inline">Sair</span>
            </Button>
          ) : (
            <Button
              variant="ghost"
              size="sm"
              onClick={() => signInWithKeycloak()}
              className="text-text-secondary hover:text-text-primary gap-1.5"
            >
              <User className="w-4 h-4" />
              <span className="hidden sm:inline">Entrar</span>
            </Button>
          )}
        </div>
      </nav>
    </header>
  );
};
