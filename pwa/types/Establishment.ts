import { Item } from "./item";

export interface ViewportBounds {
  south: number;
  west: number;
  north: number;
  east: number;
  zoom: number;
}

export interface EvaluationRating {
  criterion: string;
  rating: number;
}

export interface Evaluation {
  "@id"?: string;
  comment?: string;
  ratings: EvaluationRating[];
  netVotes?: number;
}

export interface EvaluationsSummary {
  [criterion: string]: {
    average: number;
    count: number;
  };
}

export interface Establishment extends Item {
  googlePlaceId?: string;
  name: string;
  address?: string;
  phoneNumber?: string;
  website?: string;
  location: string; // "POINT(lng lat)"
  evaluations?: Evaluation[];
  evaluationsSummary?: EvaluationsSummary;
}

