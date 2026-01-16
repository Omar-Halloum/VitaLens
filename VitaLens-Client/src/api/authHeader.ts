export function getAuthHeader(isMultipart: boolean = false) {
  const token = localStorage.getItem("token");
  const headers: Record<string, string> = {
    "Accept": "application/json",
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
  };

  if (!isMultipart) {
    headers["Content-Type"] = "application/json";
  }

  return headers;
}