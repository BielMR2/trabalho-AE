import type {Metadata} from "next";
import {type ReactNode} from "react";
import {Outfit, Work_Sans} from "next/font/google";

import {Layout} from "../components/common/Layout";
import "../styles/globals.css";
import {Providers} from "./providers";

const outfit = Outfit({
  subsets: ["latin"],
  weight: ["400", "500", "600", "700"],
  variable: "--font-heading",
  display: "swap",
});

const workSans = Work_Sans({
  subsets: ["latin"],
  weight: ["300", "400", "500"],
  variable: "--font-body",
  display: "swap",
});

export const metadata: Metadata = {
  title: "Acessibiliza — Avaliação de Acessibilidade",
  description: "Avalie e descubra a acessibilidade de estabelecimentos próximos a você.",
};

export default async function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html lang="pt-BR" className={`${outfit.variable} ${workSans.variable}`}>
      <body>
        <Providers>
          <Layout>
            {children}
          </Layout>
        </Providers>
      </body>
    </html>
  );
}
