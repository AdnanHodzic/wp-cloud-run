import { SettingType } from '@/types/globals';
interface ApiResponse {
  success: boolean;
  message?: string;
  data?: Record<string, any>;
}

class SPCApi {
  private baseUrl: string;
  private nonce?: string;

  constructor() {
    this.baseUrl = window.SPCDash.api;
    this.nonce = window.SPCDash.nonce;
  }

  private async makeRequest(endpoint: string, options: RequestInit = {}): Promise<ApiResponse> {
    const url = this.baseUrl + endpoint;

    const headers: HeadersInit = {
      'Content-Type': 'application/json',
      'X-WP-Nonce': this.nonce,
      ...options.headers,
    };

    try {
      const response = await fetch(url, {
        ...options,
        headers,
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        return {
          success: false,
          message: data.message || window.SPCDash.i18n.genericError,
        };
      }

      return data;
    } catch (error) {
      return {
        success: false,
        message: error instanceof Error ? error.message : window.SPCDash.i18n.genericError,
      };
    }
  }

  async purgeCacheAll(): Promise<ApiResponse> {
    return this.makeRequest('/cache/purge', {
      method: 'POST',
    });
  }

  async purgeCacheVarnish(): Promise<ApiResponse> {
    return this.makeRequest('/cache/purge-varnish', {
      method: 'GET',
    });
  }

  async testCache(): Promise<ApiResponse> {
    return this.makeRequest('/cache/test', {
      method: 'GET',
    });
  }

  async toggleLicenseKey(data = {}): Promise<ApiResponse> {
    return this.makeRequest('/toggle-license', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async resetSettings(): Promise<ApiResponse> {
    return this.makeRequest('/settings/reset', {
      method: 'GET',
    });
  }

  async enablePageCache(): Promise<ApiResponse> {
    return this.makeRequest('/settings/wizard', {
      method: 'GET',
    });
  }

  async clearLogs(): Promise<ApiResponse> {
    return this.makeRequest('/logs/clear', {
      method: 'GET',
    });
  }

  async getLogs(): Promise<ApiResponse> {
    return this.makeRequest('/logs/get', {
      method: 'GET',
    });
  }

  async startPreloader(): Promise<ApiResponse> {
    return this.makeRequest('/preloader/start', {
      method: 'GET',
    });
  }

  async importConfig(config: Record<string, any>): Promise<ApiResponse> {
    return this.makeRequest('/config/import', {
      method: 'POST',
      body: JSON.stringify({ settings: config })
    })
  }

  async updateSettings(data: SettingType): Promise<ApiResponse> {
    return this.makeRequest('/settings/update', {
      method: 'POST',
      body: JSON.stringify({ settings: data }),
    });
  }

  async cloudflareConnect(data: { auth_mode: string, email?: string, api_key?: string, api_token?: string }): Promise<ApiResponse> {
    return this.makeRequest('/cloudflare/connect', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async cloudflareDisconnect(): Promise<ApiResponse> {
    return this.makeRequest('/cloudflare/disconnect', {
      method: 'GET',
    });
  }

  async cloudflareConfirmZoneId(data: { zone_id: string }): Promise<ApiResponse> {
    return this.makeRequest('/cloudflare/confirm-zone-id', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async verifyTokenPermissions(): Promise<ApiResponse> {
    return this.makeRequest('/cloudflare/token-permissions', {
      method: 'GET',
    });
  }

  async databaseOptimization(data?: {action: string}): Promise<ApiResponse> {
    return this.makeRequest('/database/optimize', {
      method: 'DELETE',
      body: JSON.stringify(data),
    });
  }

  async getCloudflareAnalytics(): Promise<ApiResponse> {
    return this.makeRequest('/cloudflare/analytics', {
      method: 'GET',
    });
  }

  async repairCloudflareRule(): Promise<ApiResponse> {
    return this.makeRequest('/cloudflare/repair-rule', {
      method: 'GET',
    });
  }

  async dismissNotice(key: string): Promise<ApiResponse> {
    return this.makeRequest('/notice/dismiss', {
      method: 'POST',
      body: JSON.stringify({ key }),
    });
  }

  async getCachedPages(): Promise<ApiResponse> {
    return this.makeRequest('/cached-pages', {
      method: 'GET',
    });
  }
}

export const spcApi = new SPCApi();
export { SPCApi };
export type { ApiResponse };
