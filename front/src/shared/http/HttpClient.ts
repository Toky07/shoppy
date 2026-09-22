export type HttpQuery = Record<string, string | number | undefined>
export type HttpBody = Record<string, unknown>

export type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'

export interface HttpClient {
  get<T>(path: string, query?: HttpQuery): Promise<T>
  post<T>(path: string, body?: HttpBody): Promise<T>
  postForm<T>(path: string, body: FormData): Promise<T>
  put<T>(path: string, body?: HttpBody): Promise<T>
  patch<T>(path: string, body?: HttpBody): Promise<T>
  delete<T>(path: string): Promise<T>
}
