"use client";

import { useEffect, useState, useCallback } from "react";
import { APIProvider, Map, Marker } from "@vis.gl/react-google-maps";
import { Establishment, ViewportBounds } from "../../types/Establishment";

interface MapViewProps {
  establishments: Establishment[];
  onMarkerClick: (establishment: Establishment) => void;
  onBoundsChange?: (bounds: ViewportBounds) => void;
}

const parseLocation = (pointStr: string) => {
  if (!pointStr) return null;
  const match = pointStr.match(/POINT\(([-.\d]+)\s+([-.\d]+)\)/);
  if (match) {
    return {
      lat: parseFloat(match[2]),
      lng: parseFloat(match[1]),
    };
  }
  return null;
};

export function MapView({ establishments, onMarkerClick, onBoundsChange }: MapViewProps) {
  const [debouncedBoundsChange, setDebouncedBoundsChange] = useState<NodeJS.Timeout | null>(null);
  
  const handleCameraChange = useCallback((ev: any) => {
    if (debouncedBoundsChange) clearTimeout(debouncedBoundsChange);
    
    if (onBoundsChange && ev.detail) {
      const timeout = setTimeout(() => {
        const bounds = ev.detail.bounds;
        if (bounds) {
          onBoundsChange({
            north: bounds.north,
            south: bounds.south,
            east: bounds.east,
            west: bounds.west,
            zoom: ev.detail.zoom,
          });
        }
      }, 500);
      setDebouncedBoundsChange(timeout);
    }
  }, [onBoundsChange, debouncedBoundsChange]);
  return (
    <div className="flex-1 h-full w-full">
      <APIProvider apiKey={process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY || ""}>
        <Map
          defaultCenter={{ lat: -14.235, lng: -51.925 }}
          defaultZoom={4}
          onCameraChanged={handleCameraChange}
          disableDefaultUI={true}
          styles={[
            {
              featureType: "poi",
              stylers: [{ visibility: "off" }],
            },
            {
              featureType: "transit",
              elementType: "labels.icon",
              stylers: [{ visibility: "off" }],
            }
          ]}
        >
          {establishments.map((est) => {
            const position = parseLocation(est.location);
            if (!position) return null;

            return (
              <Marker
                key={est["@id"] || est.id}
                position={position}
                onClick={() => onMarkerClick(est)}
              />
            );
          })}
        </Map>
      </APIProvider>
    </div>
  );
}

