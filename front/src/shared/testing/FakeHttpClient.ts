import type { HttpBody, HttpClient, HttpQuery } from '@/shared/http/HttpClient'

export type FakeHttpRequest = {
  method: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'
  path: string
  query?: HttpQuery
  body?: HttpBody | FormData
}

export class FakeHttpClient implements HttpClient {
  public readonly calls: FakeHttpRequest[] = []

  constructor(private readonly handler: (request: FakeHttpRequest) => Promise<unknown> | unknown) {}

  async get<T>(path: string, query?: HttpQuery): Promise<T> {
    const request: FakeHttpRequest = { method: 'GET', path, query }
    this.calls.push(request)
    return (await this.handler(request)) as T
  }

  async post<T>(path: string, body?: HttpBody): Promise<T> {
    return this.send('POST', path, body)
  }

  async postForm<T>(path: string, body: FormData): Promise<T> {
    return this.send('POST', path, body)
  }

  async put<T>(path: string, body?: HttpBody): Promise<T> {
    return this.send('PUT', path, body)
  }

  async patch<T>(path: string, body?: HttpBody): Promise<T> {
    return this.send('PATCH', path, body)
  }

  async delete<T>(path: string): Promise<T> {
    return this.send('DELETE', path)
  }

  private async send<T>(method: FakeHttpRequest['method'], path: string, body?: HttpBody | FormData): Promise<T> {
    const request: FakeHttpRequest = { method, path, body }
    this.calls.push(request)
    return (await this.handler(request)) as T
  }
}
