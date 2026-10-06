import { proxyJson } from "@/lib/proxy-route";

export async function POST(request: Request) {
  return proxyJson(request, "/checkout");
}
