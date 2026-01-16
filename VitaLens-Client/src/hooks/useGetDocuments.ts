import { useQuery } from "@tanstack/react-query";
import { fetchDocuments } from "../api/documents";
import type { Document } from "../types/document";

export const useGetDocuments = () =>
  useQuery<Document[], Error>({
    queryKey: ['documents'],
    queryFn: fetchDocuments,
    staleTime: 5 * 60 * 1000,
  });