import { AlertCircle, X } from "lucide-react";

interface Props {
  message: string;
  onDismiss?: () => void;
}

export const Error = ({ message, onDismiss }: Props) => (
  <div
    role="alert"
    className="flex items-center gap-3 p-4 mb-4 text-danger bg-danger-light border border-danger/20 rounded-[var(--radius-md)]"
  >
    <AlertCircle className="shrink-0 w-5 h-5" aria-hidden="true" />
    <p className="flex-1 text-sm font-medium">{message}</p>
    {onDismiss && (
      <button
        type="button"
        onClick={onDismiss}
        aria-label="Fechar alerta"
        className="shrink-0 p-1 rounded hover:bg-danger/10 transition-colors"
      >
        <X className="w-4 h-4" />
      </button>
    )}
  </div>
);
