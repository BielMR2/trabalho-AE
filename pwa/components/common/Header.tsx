"use client";

import Link from "next/link";
import PersonOutlineIcon from "@mui/icons-material/PersonOutlined";

import { useSession, signInWithKeycloak, signOutWithKeycloak } from "../../hooks/useAuth";

export const Header = () => {
  const { data: session, isPending } = useSession();

  return (
    <header className="bg-white border-b border-gray-200 sticky top-0 z-30">
      <nav className="flex items-center justify-between px-6 py-3" aria-label="Global">
        <div className="text-xl font-bold">
          <Link href="/" className="text-gray-900 hover:text-cyan-700 transition-colors">
            Acessibiliza
          </Link>
        </div>
        <div className="flex items-center gap-4">
          {!isPending && session ? (
            <a
              href="#"
              className="font-semibold text-sm text-gray-700 hover:text-gray-900 transition-colors"
              role="menuitem"
              onClick={(e) => {
                e.preventDefault();
                signOutWithKeycloak(`${window.location.origin}/`);
              }}
            >
              Sign out
            </a>
          ) : (
            <a
              href="#"
              className="font-semibold text-sm text-gray-700 hover:text-gray-900 flex items-center gap-1 transition-colors"
              role="menuitem"
              onClick={(e) => {
                e.preventDefault();
                signInWithKeycloak();
              }}
            >
              <PersonOutlineIcon className="w-5 h-5" />
              Login
            </a>
          )}
        </div>
      </nav>
    </header>
  );
};
