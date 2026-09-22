import { ApiError } from './ApiError'
import type { AccessTokenProvider } from './AccessTokenProvider'
import type { HttpBody, HttpClient, HttpMethod, HttpQuery } from './HttpClient'
import { joinUrl } from './joinUrl'
import { parseApiError } from './parseApiError'
import { withQuery } from './withQuery'

export class FetchHttpClient implements HttpClient {
  constructor(
    private readonly baseUrl: string,
    private readonly fetchFn: typeof fetch = globalThis.fetch.bind(globalThis),
    private readonly tokenProvider: AccessTokenProvider | null = null,
  ) {}

  get<T>(path: string, query?: HttpQuery): Promise<T> {
    return this.request('GET', withQuery(path, query))
  }

  post<T>(path: string, body?: HttpBody): Promise<T> {
    return this.request('POST', path, body)
  }

  postForm<T>(path: string, body: FormData): Promise<T> {
    return this.request('POST', path, body)
  }

  put<T>(path: string, body?: HttpBody): Promise<T> {
    return this.request('PUT', path, body)
  }

  patch<T>(path: string, body?: HttpBody): Promise<T> {
    return this.request('PATCH', path, body)
  }

  delete<T>(path: string): Promise<T> {
    return this.request('DELETE', path)
  }

  private async request<T>(method: HttpMethod, path: string, body?: HttpBody | FormData): Promise<T> {
    const response = await this.fetchFn(joinUrl(this.baseUrl, path), {
      method,
      headers: this.headers(body !== undefined && !(body instanceof FormData)),
      ...(body === undefined ? {} : { body: body instanceof FormData ? body : JSON.stringify(body) }),
    })

    if (!response.ok) {
      const error = await this.toApiError(response)

      if (error.code === 'unauthenticated') {
        this.tokenProvider?.clear?.()
      }

      throw error
    }

    if (response.status === 204) {
      return undefined as T
    }

    return (await response.json()) as T
  }

  private headers(hasBody: boolean): Record<string, string> {
    const headers: Record<string, string> = { Accept: 'application/json' }

    if (hasBody) {
      headers['Content-Type'] = 'application/json'
    }

    const token = this.tokenProvider?.current()
    if (token) {
      headers.Authorization = `Bearer ${token}`
    }

    return headers
  }

  private async toApiError(response: Response): Promise<ApiError> {
    try {
      return parseApiError(response.status, await response.json())
    } catch {
      return parseApiError(response.status, null)
    }
  }
}
