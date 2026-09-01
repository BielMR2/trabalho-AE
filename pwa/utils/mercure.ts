import { useEffect, useState } from "react";
import { type Item } from "../types/item";
import { type PagedCollection } from "../types/collection";
import { isItem } from "../types/item";
import { isPagedCollection } from "../types/collection";

const mercureSubscribe = <T extends Item | PagedCollection<Item> | null | undefined>(
  hubURL: string,
  data: T | PagedCollection<T>,
  setData: (data: T) => void
) => {
  if (!data || !data["@id"]) throw new Error("@id is missing");

  const url = new URL(hubURL, window.origin);
  url.searchParams.append(
    "topic",
    new URL(data["@id"], window.origin).toString()
  );
  const eventSource = new EventSource(url.toString());
  eventSource.addEventListener("message", (event) =>
    setData(JSON.parse(event.data))
  );

  return eventSource;
};

export const useMercure = <
  TData extends Item | PagedCollection<Item> | null | undefined
>(
  deps: TData,
  hubURL: string | null | undefined
): TData => {
  const [prevDeps, setPrevDeps] = useState(deps);
  const [data, setData] = useState(deps);
  if (deps !== prevDeps) {
    setPrevDeps(deps);
    setData(deps);
  }

  useEffect(() => {
    if (!hubURL || !data) {
      return;
    }

    if (!isPagedCollection<Item>(data) && !isItem(data)) {
      console.error("Object sent is not in JSON-LD format.");

      return;
    }

    if (
      isPagedCollection<Item>(data) &&
      data["member"] &&
      data["member"].length !== 0
    ) {
      // It's a PagedCollection. Group all topics into a single connection.
      const url = new URL(hubURL, window.origin);
      data["member"].forEach((obj) => {
        if (obj["@id"]) {
          url.searchParams.append("topic", new URL(obj["@id"], window.origin).toString());
        }
      });

      const eventSource = new EventSource(url.toString());
      eventSource.addEventListener("message", (event) => {
        const datum = JSON.parse(event.data);
        if (data["member"]) {
          const index = data["member"].findIndex((item) => item["@id"] === datum["@id"]);
          if (index !== -1) {
            data["member"][index] = datum;
            setData({ ...data });
          }
        }
      });

      return () => {
        eventSource.close();
      };
    }

    // It's a single object
    const eventSource = mercureSubscribe<TData>(hubURL, data, setData);

    return () => {
      eventSource.close();
    };
  }, [data, hubURL]);

  return data;
};
