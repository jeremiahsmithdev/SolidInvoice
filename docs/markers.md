/init

You are an expert in Symfony 7, Symfony UX (Stimulus, Leaflet), and PHP/JavaScript. In the SolidInvoice app, we already have a Leaflet map showing client markers (currently rendering as squares). We need to enhance the map by integrating Jobs as follows:

1. **Marker improvements**:
   - Replace square markers with color-coded markers/icons.
   - Clients: default color (e.g. blue).
   - Jobs:
     * Upcoming / unscheduled: e.g. red.
     * In-progress: e.g. orange.
     * Completed jobs should be archived by default (not shown), but visible via a filter checkbox (e.g. grey markers).

2. **Filtering**:
   - Add map controls or checkboxes for:
     * Show jobs toggle (on/off).
     * Show clients toggle (on/off).
     * Show archived/completed toggle.
   - Filtering should dynamically refresh markers without page reload (AJAX or LiveComponent).

3. **Legend/UI**:
   - Add a small legend overlay explaining colors.
   - Tooltips/popups when clicking a job marker: show job title, client name, scheduled date, status, and link to job/invoice.

4. **Backend/API support**:
   - Create or reuse an API endpoint returning all visible jobs with geolocation and status.
   - Filter by status if needed (jobs vs clients).
   - Ensure upcoming jobs appear first.

5. **UX polish**:
   - Animate marker changes on filter toggle (e.g. fade markers in/out).
   - Optionally cluster markers if many jobs in same area.
   - Auto-center map on upcoming jobs if filter active.

TOOLS: Use `Edit` to update Leaflet map JS and PHP/API code. Use `Bash` for asset rebuilds and clearing cache. Use API Platform for endpoint. Use LiveComponent or Stimulus for dynamic map updates.

Important constraints:
- Don’t break existing map functionality.
- Keep UI minimal and intuitive.
- Ensure marker icons/colors are consistent and accessible.

Phase 1: Improve marker shapes/colors and add job markers with correct status filtering. No clustering or animation yet.

Once done, I’ll review and then we’ll add filters, legend, and dynamic updates.
