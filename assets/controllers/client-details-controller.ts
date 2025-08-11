import { Controller } from "@hotwired/stimulus"

interface ClientData {
  id: string;
  firstName: string;
  lastName: string;
  email: string;
  phone?: string;
  addresses: string[];
}

interface AddressData {
  street1: string;
  street2?: string;
  city: string;
  state: string;
  zip: string;
  countryName: string;
}

/**
 * Client Details Controller
 * 
 * Fetches and displays client information when a client is selected
 * in quote and invoice creation forms. Replaces server-side rendering
 * with dynamic API-based client detail loading.
 */
export default class extends Controller {
  static targets = ["details", "contact", "address", "clientName", "changeLink"]
  static values = { 
    apiToken: String,
    apiUrl: String 
  }

  declare readonly detailsTarget: HTMLElement
  declare readonly contactTarget: HTMLElement
  declare readonly addressTarget: HTMLElement
  declare readonly clientNameTarget: HTMLElement
  declare readonly changeLinkTarget: HTMLElement
  declare readonly apiTokenValue: string
  declare readonly apiUrlValue: string

  private clientField: HTMLInputElement | HTMLSelectElement | null = null

  connect() {
    console.log('Client details controller connected')
    this.findClientField()
    this.setupEventListeners()
    this.checkInitialClientValue()
  }

  disconnect() {
    this.removeEventListeners()
  }

  /**
   * Find the client field element
   */
  private findClientField(): void {
    // Try multiple selectors for different field types
    const selectors = [
      'select[name*="[client]"]',
      'input[name*="[client]"]',
      '.client-select'
    ]

    for (const selector of selectors) {
      const field = this.element.querySelector(selector) as HTMLInputElement | HTMLSelectElement
      if (field) {
        this.clientField = field
        console.log('Found client field:', field)
        break
      }
    }

    if (!this.clientField) {
      console.warn('Client field not found')
    }
  }

  /**
   * Set up event listeners for client field changes
   */
  private setupEventListeners(): void {
    if (this.clientField) {
      this.clientField.addEventListener('change', this.handleClientChange.bind(this))
      this.clientField.addEventListener('input', this.handleClientInput.bind(this))
    }
  }

  /**
   * Remove event listeners
   */
  private removeEventListeners(): void {
    if (this.clientField) {
      this.clientField.removeEventListener('change', this.handleClientChange.bind(this))
      this.clientField.removeEventListener('input', this.handleClientInput.bind(this))
    }
  }

  /**
   * Handle client change events
   */
  private handleClientChange(event: Event): void {
    const target = event.target as HTMLInputElement | HTMLSelectElement
    console.log('Client change detected:', target.value)
    
    if (!target.value || target.value === '') {
      this.hideDetails()
    } else {
      this.loadClientDetails(target.value)
    }
  }

  /**
   * Handle client input events (for autocomplete)
   */
  private handleClientInput(event: Event): void {
    const target = event.target as HTMLInputElement
    console.log('Client input detected:', target.value)
    
    // Delay to allow autocomplete to complete
    setTimeout(() => {
      if (this.isValidId(target.value)) {
        this.loadClientDetails(target.value)
      } else if (!target.value || target.value === '') {
        this.hideDetails()
      }
    }, 300)
  }

  /**
   * Check if the value looks like a valid ID (ULID)
   */
  private isValidId(value: string): boolean {
    return !!(value && value.length > 10) // Basic ULID validation
  }

  /**
   * Load client details from API
   */
  private async loadClientDetails(clientId: string): Promise<void> {
    if (!this.isValidId(clientId)) {
      this.hideDetails()
      return
    }

    try {
      console.log('Fetching client details for ID:', clientId)
      
      const response = await fetch(`${this.apiUrlValue}/api/clients/${clientId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-API-TOKEN': this.apiTokenValue
        }
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const clientData: ClientData = await response.json()
      console.log('Client data received:', clientData)

      if (clientData && clientData.id) {
        await this.displayClientDetails(clientData)
        this.showDetails()
      }
    } catch (error) {
      console.error('Error fetching client details:', error)
      this.hideDetails()
    }
  }

  /**
   * Display client details in the UI
   */
  private async displayClientDetails(client: ClientData): Promise<void> {
    const contactInfo: string[] = []
    const addressInfo: string[] = []

    // Display client name
    const clientName = [client.firstName, client.lastName].filter(n => n).join(' ')
    this.clientNameTarget.innerHTML = clientName || 'Unknown Client'

    // Display basic client info
    if (client.email) {
      contactInfo.push(`<div class="contact-detail"><strong>Email:</strong> ${client.email}</div>`)
    }

    if (client.phone) {
      contactInfo.push(`<div class="contact-detail"><strong>Phone:</strong> ${client.phone}</div>`)
    }

    // Fetch addresses if they are IRIs
    if (client.addresses && client.addresses.length > 0) {
      for (const addressIri of client.addresses) {
        if (typeof addressIri === 'string' && addressIri.startsWith('/api/')) {
          try {
            const addressData = await this.fetchAddress(addressIri)
            if (addressData) {
              const addressParts: string[] = []
              
              if (addressData.street1) addressParts.push(addressData.street1)
              if (addressData.street2) addressParts.push(addressData.street2)
              
              const cityStateZip: string[] = []
              if (addressData.city) cityStateZip.push(addressData.city)
              if (addressData.state) cityStateZip.push(addressData.state)
              if (addressData.zip) cityStateZip.push(addressData.zip)
              
              if (cityStateZip.length > 0) {
                addressParts.push(cityStateZip.join(', '))
              }
              
              if (addressData.countryName) addressParts.push(addressData.countryName)
              
              if (addressParts.length > 0) {
                addressInfo.push(`
                  <div class="address-info">
                    <strong>Address:</strong>
                    <div class="address-details">
                      ${addressParts.join('<br>')}
                    </div>
                  </div>
                `)
              }
            }
          } catch (error) {
            console.error('Error fetching address:', error)
          }
        }
      }
    }

    // Update contact info
    this.contactTarget.innerHTML = contactInfo.length > 0 
      ? contactInfo.join('') 
      : '<div class="text-muted">No contact information available</div>'
    
    // Update address info
    this.addressTarget.innerHTML = addressInfo.length > 0 
      ? addressInfo.join('') 
      : '<div class="text-muted">No address available</div>'
  }

  /**
   * Fetch address data from API
   */
  private async fetchAddress(addressIri: string): Promise<AddressData | null> {
    const response = await fetch(`${this.apiUrlValue}${addressIri}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-API-TOKEN': this.apiTokenValue
      }
    })

    if (response.ok) {
      return response.json()
    }

    return null
  }

  /**
   * Handle client clear action (when "change" link is clicked)
   */
  clearClient(): void {
    if (this.clientField) {
      this.clientField.value = ''
      
      // Trigger change event to notify other listeners
      const changeEvent = new Event('change', { bubbles: true })
      this.clientField.dispatchEvent(changeEvent)
    }
    
    this.hideDetails()
  }


  /**
   * Show the client details panel
   */
  private showDetails(): void {
    this.detailsTarget.style.display = 'block'
  }

  /**
   * Hide the client details panel
   */
  private hideDetails(): void {
    this.detailsTarget.style.display = 'none'
  }

  /**
   * Check if there's an initial client value and load details
   */
  private checkInitialClientValue(): void {
    if (this.clientField && this.clientField.value && this.isValidId(this.clientField.value)) {
      this.loadClientDetails(this.clientField.value)
    }
  }

}