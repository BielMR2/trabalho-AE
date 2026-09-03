"use client";

import { useEffect, useRef, useState } from "react";
import { Map, useMap, AdvancedMarker } from "@vis.gl/react-google-maps";
import { ViewportBounds, Establishment } from "@/types/Establishment";
import { Loader2 } from "lucide-react";

interface MapViewProps {
  establishments: Establishment[];
  isLoading: boolean;
  onMarkerClick: (establishment: Establishment) => void;
  selectedEstablishment: Establishment | null;
  onViewportChange: (viewport: ViewportBounds) => void;
  showZoomMessage: boolean;
}

export default function MapView({
  establishments,
  isLoading,
  onMarkerClick,
  selectedEstablishment,
  onViewportChange,
  showZoomMessage,
}: MapViewProps) {
  const map = useMap();
  const debounceRef = useRef<NodeJS.Timeout>();
  const [center, setCenter] = useState({ lat: -23.5505, lng: -46.6333 });

  useEffect(() => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition((position) => {
        setCenter({ lat: position.coords.latitude, lng: position.coords.longitude });
      });
    }
  }, []);

  useEffect(() => {
    if (map && selectedEstablishment) {
      const loc = parseGeometry(selectedEstablishment.location);
      if (loc) {
        map.panTo(loc);
      }
    }
  }, [map, selectedEstablishment]);

  const handleBoundsChanged = () => {
    if (!map) return;
    
    if (debounceRef.current) clearTimeout(debounceRef.current);
    
    debounceRef.current = setTimeout(() => {
      const bounds = map.getBounds();
      const zoom = map.getZoom();
      
      if (bounds && zoom !== undefined) {
        const ne = bounds.getNorthEast();
        const sw = bounds.getSouthWest();
        
        onViewportChange({
          north: ne.lat(),
          east: ne.lng(),
          south: sw.lat(),
          west: sw.lng(),
          zoom,
        });
      }
    }, 400);
  };

  const parseGeometry = (wkt?: string) => {
    if (!wkt) return null;
    const match = wkt.match(/POINT\s*\(\s*([-\d.]+)\s+([-\d.]+)\s*\)/i);
    if (match) {
      return { lng: parseFloat(match[1]), lat: parseFloat(match[2]) };
    }
    return null;
  };

  return (
    <div className="relative w-full h-full">
      <Map
        defaultZoom={15}
        center={center}
        onCenterChanged={(e) => setCenter(e.detail.center)}
        onBoundsChanged={handleBoundsChanged}
        onIdle={handleBoundsChanged}
        mapId="primary-map"
        disableDefaultUI={true}
        gestureHandling="greedy"
        styles={[
          {
            featureType: "poi",
            stylers: [{ visibility: "off" }],
          },
          {
            featureType: "transit",
            stylers: [{ visibility: "off" }],
          },
        ]}
      >
        {establishments.map((est) => {
          const loc = parseGeometry(est.location);
          if (!loc) return null;
          return (
            <AdvancedMarker
              key={est["@id"]}
              position={loc}
              onClick={() => onMarkerClick(est)}
            >
              <div className={`w-6 h-6 rounded-full border-2 border-white shadow-sm transition-transform ${selectedEstablishment?.["@id"] === est["@id"] ? 'bg-primary-900 scale-125' : 'bg-primary-700 hover:scale-110'}`} />
            </AdvancedMarker>
          );
        })}
      </Map>

      {showZoomMessage && (
        <div className="absolute top-4 left-1/2 -translate-x-1/2 bg-surface-card px-4 py-2 rounded-full shadow-md text-sm font-medium text-text-primary border border-border">
          Aproxime o mapa para ver os locais
        </div>
      )}

      {isLoading && (
        <div className="absolute inset-0 bg-surface/20 flex items-center justify-center pointer-events-none">
          <div className="bg-surface-card p-3 rounded-full shadow-lg">
            <Loader2 className="animate-spin text-primary-700" size={24} />
          </div>
        </div>
      )}
    </div>
  );
}
