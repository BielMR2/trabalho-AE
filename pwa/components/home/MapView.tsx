"use client";

import { useState, useEffect, useCallback } from "react";
import { APIProvider, Map, AdvancedMarker, Pin } from "@vis.gl/react-google-maps";
import { Establishment } from "@/types/Establishment";

function parseGeometry(pointString?: string | null): { lat: number; lng: number } | null {
  if (!pointString) return null;
  const match = pointString.match(/POINT\(([-.\d]+) ([-.\d]+)\)/);
  if (match) {
    return { lat: parseFloat(match[2]), lng: parseFloat(match[1]) };
  }
  return null;
}

const DEFAULT_CENTER = { lat: -23.5505, lng: -46.6333 }; // São Paulo

const MAP_STYLES = [
  {
    featureType: "poi",
    stylers: [{ visibility: "off" }],
  },
  {
    featureType: "transit",
    stylers: [{ visibility: "off" }],
  },
];

interface MapViewProps {
  establishments: Establishment[];
  isLoading?: boolean;
  onMarkerClick: (establishment: Establishment) => void;
}

export default function MapView({ establishments, isLoading, onMarkerClick }: MapViewProps) {
  const [userLocation, setUserLocation] = useState(DEFAULT_CENTER);

  useEffect(() => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          setUserLocation({
            lat: position.coords.latitude,
            lng: position.coords.longitude,
          });
        },
        () => {
          // Geolocation denied or failed, keep default
        }
      );
    }
  }, []);

  return (
    <div className="relative flex-1 h-full">
      <APIProvider apiKey={process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY || ""}>
        <Map
          style={{ width: "100%", height: "100%" }}
          defaultCenter={userLocation}
          center={userLocation}
          defaultZoom={13}
          gestureHandling="greedy"
          disableDefaultUI={false}
          zoomControl={true}
          streetViewControl={false}
          mapTypeControl={false}
          fullscreenControl={false}
          styles={MAP_STYLES}
        >
          {establishments.map((est) => {
            const coords = parseGeometry(est.location);
            if (!coords) return null;

            return (
              <AdvancedMarker
                key={est["@id"]}
                position={coords}
                onClick={() => onMarkerClick(est)}
                title={est.name}
              >
                <Pin
                  background="#0f929a"
                  glyphColor="#ffffff"
                  borderColor="#0a6b71"
                />
              </AdvancedMarker>
            );
          })}
        </Map>
      </APIProvider>

      {/* Loading overlay */}
      {isLoading && (
        <div className="absolute inset-0 bg-white/50 flex items-center justify-center z-10 pointer-events-none">
          <div className="bg-white px-4 py-2 rounded-lg shadow-md text-sm text-gray-600">
            Carregando estabelecimentos...
          </div>
        </div>
      )}
    </div>
  );
}
