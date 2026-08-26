$(function () {
  // ========== AUTH ==========
  function checkAuth() {
    $.getJSON('api/auth.php?action=me', function (res) {
      if (res.logged_in) {
        $('#authScreen').addClass('hidden');
        $('#appScreen').removeClass('hidden');
        $('#userName').text(res.user.name);
      } else {
        $('#authScreen').removeClass('hidden');
        $('#appScreen').addClass('hidden');
      }
    });
  }
  checkAuth();

  $('#showRegister').on('click', e => { e.preventDefault(); $('#loginForm').addClass('hidden'); $('#registerForm').removeClass('hidden'); });
  $('#showLogin').on('click', e => { e.preventDefault(); $('#registerForm').addClass('hidden'); $('#loginForm').removeClass('hidden'); });

  $('#btnLogin').on('click', function () {
    $.ajax({
      url: 'api/auth.php?action=login', method: 'POST', contentType: 'application/json',
      data: JSON.stringify({ email: $('#loginEmail').val(), password: $('#loginPass').val() }),
      success: res => {
        if (res.success) checkAuth();
        else $('#authError').text(res.error || 'Login failed');
      }
    });
  });

  $('#btnRegister').on('click', function () {
    $.ajax({
      url: 'api/auth.php?action=register', method: 'POST', contentType: 'application/json',
      data: JSON.stringify({ name: $('#regName').val(), email: $('#regEmail').val(), password: $('#regPass').val() }),
      success: res => {
        if (res.success) checkAuth();
        else $('#authError').text(res.error || 'Registration failed');
      }
    });
  });

  $('#btnLogout').on('click', () => {
    $.get('api/auth.php?action=logout', () => location.reload());
  });

  // Settings
  $('#btnSettings').on('click', () => $('#settingsModal').removeClass('hidden').css('display','flex'));
  $('#btnCloseSettings').on('click', () => $('#settingsModal').addClass('hidden').css('display','none'));
  $('#btnSaveApi').on('click', function () {
    $.ajax({
      url: 'api/auth.php?action=save_api', method: 'POST', contentType: 'application/json',
      data: JSON.stringify({ api_key: $('#apiKey').val(), api_base: $('#apiBase').val() }),
      success: () => {
        $('#apiStatus').removeClass('hidden');
        setTimeout(() => $('#apiStatus').addClass('hidden'), 2000);
      }
    });
  });

  // ========== TABS ==========
  $('.tab').on('click', function () {
    $('.tab').removeClass('active');
    $(this).addClass('active');
    $('.tab-content').addClass('hidden');
    $('#tab-' + $(this).data('tab')).removeClass('hidden');
    if ($(this).data('tab') === 'meetings') loadMeetings();
    if ($(this).data('tab') === 'actions') loadActions();
  });

  // ========== PROMPT + AI ==========
  const basePrompt = `You are an expert meeting minutes specialist. Turn raw notes into clean professional minutes.
Never invent facts. Prioritize Decisions and Action Items.
Return structured Markdown with: Executive Summary, Key Discussion Points, Decisions, Action Items table, Open Questions, Next Steps.`;

  $('#btnGeneratePrompt').on('click', function () {
    const notes = $('#raw_notes').val().trim();
    if (!notes) return alert('Paste notes first');
    let meta = '';
    if ($('#title').val()) meta += `Title: ${$('#title').val()}\n`;
    if ($('#meeting_date').val()) meta += `Date: ${$('#meeting_date').val()}\n`;
    $('#promptBox').val(basePrompt + '\n\n' + meta + '\n' + notes);
    $('#promptCard').removeClass('hidden');
  });

  $('#btnCopyPrompt').on('click', function () {
    const t = $('#promptBox').val();
    if (!t) return alert('Generate prompt first');
    navigator.clipboard.writeText(t);
    $('#aiStatus').text('Prompt copied!').removeClass('hidden');
    setTimeout(() => $('#aiStatus').addClass('hidden'), 2000);
  });

  $('#btnAI').on('click', function () {
    const notes = $('#raw_notes').val().trim();
    if (!notes) return alert('Paste notes first');
    $('#aiStatus').text('Calling AI... please wait').removeClass('hidden');
    $('#btnAI').prop('disabled', true);

    $.ajax({
      url: 'api/ai.php', method: 'POST', contentType: 'application/json',
      data: JSON.stringify({
        notes: notes,
        title: $('#title').val(),
        date: $('#meeting_date').val()
      }),
      success: function (res) {
        $('#btnAI').prop('disabled', false);
        if (res.error) {
          $('#aiStatus').text(res.error);
          return;
        }
        const d = res.data;
        $('#executive_summary').val(d.executive_summary || '');
        $('#discussion_points').val(d.discussion_points || '');
        $('#decisions').val(d.decisions || '');
        $('#open_questions').val(d.open_questions || '');
        $('#next_steps').val(d.next_steps || '');

        // Clear and fill action items
        $('#actionItemsForm').empty();
        actionCount = 0;
        if (d.action_items && d.action_items.length) {
          d.action_items.forEach(a => addActionRow(a));
        } else {
          addActionRow();
        }
        $('#aiStatus').text('AI summary ready! Review and save.').removeClass('hidden');
        setTimeout(() => $('#aiStatus').addClass('hidden'), 4000);
      },
      error: function () {
        $('#btnAI').prop('disabled', false);
        $('#aiStatus').text('AI request failed. Check API key in Settings.');
      }
    });
  });

  // ========== ACTION ROWS ==========
  let actionCount = 0;
  function addActionRow(data = {}) {
    actionCount++;
    const id = 'action-' + actionCount;
    $('#actionItemsForm').append(`
      <div class="action-row" id="${id}" style="border:1px solid var(--border);border-radius:8px;padding:1rem;margin-bottom:0.8rem;">
        <div class="row">
          <div style="grid-column:1/-1"><label>Action</label><input type="text" class="a-text" value="${data.action_text||''}" /></div>
          <div><label>Owner</label><input type="text" class="a-owner" value="${data.owner||''}" /></div>
          <div><label>Deadline</label><input type="date" class="a-deadline" value="${data.deadline||''}" /></div>
          <div><label>Priority</label>
            <select class="a-priority">
              <option ${data.priority==='High'?'selected':''}>High</option>
              <option ${!data.priority||data.priority==='Medium'?'selected':''}>Medium</option>
              <option ${data.priority==='Low'?'selected':''}>Low</option>
            </select>
          </div>
          <div><label>Status</label>
            <select class="a-status">
              <option ${!data.status||data.status==='Open'?'selected':''}>Open</option>
              <option ${data.status==='In Progress'?'selected':''}>In Progress</option>
              <option ${data.status==='Done'?'selected':''}>Done</option>
            </select>
          </div>
        </div>
        <button class="btn btn-danger btn-sm remove-action" data-id="${id}">Remove</button>
      </div>`);
  }
  $('#btnAddAction').on('click', () => addActionRow());
  $(document).on('click', '.remove-action', function () { $('#' + $(this).data('id')).remove(); });
  addActionRow();

  // ========== SAVE ==========
  $('#btnSaveMeeting').on('click', function () {
    const title = $('#title').val().trim();
    if (!title) return alert('Title required');
    const action_items = [];
    $('.action-row').each(function () {
      const text = $(this).find('.a-text').val().trim();
      if (text) action_items.push({
        action_text: text,
        owner: $(this).find('.a-owner').val().trim() || 'Unassigned',
        deadline: $(this).find('.a-deadline').val() || null,
        priority: $(this).find('.a-priority').val(),
        status: $(this).find('.a-status').val()
      });
    });
    $.ajax({
      url: 'api/meetings.php', method: 'POST', contentType: 'application/json',
      data: JSON.stringify({
        title, meeting_date: $('#meeting_date').val() || null, location: null,
        organizer: $('#organizer').val(), attendees: $('#attendees').val(),
        executive_summary: $('#executive_summary').val(),
        discussion_points: $('#discussion_points').val(),
        decisions: $('#decisions').val(),
        open_questions: $('#open_questions').val(),
        next_steps: $('#next_steps').val(),
        raw_notes: $('#raw_notes').val(),
        action_items
      }),
      success: res => {
        if (res.success) {
          $('#saveStatus').removeClass('hidden');
          setTimeout(() => $('#saveStatus').addClass('hidden'), 3000);
        } else alert(res.error || 'Save failed');
      },
      error: () => alert('Server error — are you logged in?')
    });
  });

  // ========== MEETINGS LIST ==========
  function loadMeetings(q = '') {
    const url = q ? 'api/meetings.php?q=' + encodeURIComponent(q) : 'api/meetings.php';
    $.getJSON(url, function (data) {
      if (!data.length) {
        $('#meetingsList').html('<div class="empty">No meetings yet.</div>');
        return;
      }
      let html = '';
      data.forEach(m => {
        html += `<div class="meeting-list-item" data-id="${m.id}">
          <div><h3>${esc(m.title)}</h3>
          <div class="meeting-meta">${m.meeting_date||'No date'} · ${esc(m.organizer||'')}</div></div>
          <span style="color:var(--muted)">View →</span></div>`;
      });
      $('#meetingsList').html(html);
    }).fail(() => $('#meetingsList').html('<div class="empty">Could not load (login required)</div>'));
  }

  $('#btnSearch').on('click', () => loadMeetings($('#searchMeetings').val()));
  $('#searchMeetings').on('keypress', e => { if (e.which === 13) loadMeetings($('#searchMeetings').val()); });

  $(document).on('click', '.meeting-list-item', function () {
    const id = $(this).data('id');
    $.getJSON('api/meetings.php?id=' + id, function (m) {
      if (m.error) return;
      $('#detailTitle').text(m.title);
      let c = `<p><strong>Date:</strong> ${m.meeting_date||'—'} | <strong>Organizer:</strong> ${esc(m.organizer||'—')}</p>
        <p><strong>Attendees:</strong> ${esc(m.attendees||'—')}</p>
        <h3 style="margin:1rem 0 0.5rem;color:var(--accent)">Executive Summary</h3>
        <p>${esc(m.executive_summary||'—').replace(/\n/g,'<br>')}</p>
        <h3 style="margin:1rem 0 0.5rem;color:var(--accent)">Decisions</h3>
        <p>${esc(m.decisions||'—').replace(/\n/g,'<br>')}</p>
        <h3 style="margin:1rem 0 0.5rem;color:var(--accent)">Action Items</h3>`;
      if (m.action_items?.length) {
        c += '<table><thead><tr><th>Action</th><th>Owner</th><th>Deadline</th><th>Priority</th><th>Status</th></tr></thead><tbody>';
        m.action_items.forEach(a => {
          c += `<tr><td>${esc(a.action_text)}</td><td>${esc(a.owner)}</td><td>${a.deadline||'—'}</td>
            <td><span class="badge badge-${a.priority.toLowerCase()}">${a.priority}</span></td>
            <td><span class="badge badge-${a.status==='Done'?'done':a.status==='In Progress'?'progress':'open'}">${a.status}</span></td></tr>`;
        });
        c += '</tbody></table>';
      } else c += '<p>—</p>';
      $('#detailContent').html(c);
      $('#btnExportMd').attr('href', 'api/export.php?id=' + id + '&format=md');
      $('#btnExportHtml').attr('href', 'api/export.php?id=' + id + '&format=html');
      $('#meetingDetail').removeClass('hidden').data('id', id);
    });
  });

  $('#btnCloseDetail').on('click', () => $('#meetingDetail').addClass('hidden'));
  $('#btnDeleteMeeting').on('click', function () {
    if (!confirm('Delete this meeting?')) return;
    $.ajax({ url: 'api/meetings.php?id=' + $('#meetingDetail').data('id'), method: 'DELETE',
      success: () => { $('#meetingDetail').addClass('hidden'); loadMeetings(); } });
  });

  // ========== ACTIONS ==========
  function loadActions(status = '') {
    const url = status ? 'api/actions.php?status=' + encodeURIComponent(status) : 'api/actions.php';
    $.getJSON(url, function (data) {
      if (!data.length) {
        $('#actionsTable').html('<tr><td colspan="7" class="empty">No items</td></tr>');
        return;
      }
      let html = '';
      data.forEach(a => {
        html += `<tr data-id="${a.id}">
          <td>${esc(a.action_text)}</td><td>${esc(a.owner)}</td><td>${a.deadline||'—'}</td>
          <td><span class="badge badge-${a.priority.toLowerCase()}">${a.priority}</span></td>
          <td><select class="status-select" style="width:auto;margin:0;padding:0.3rem;">
            <option ${a.status==='Open'?'selected':''}>Open</option>
            <option ${a.status==='In Progress'?'selected':''}>In Progress</option>
            <option ${a.status==='Done'?'selected':''}>Done</option>
            <option ${a.status==='Cancelled'?'selected':''}>Cancelled</option>
          </select></td>
          <td>${esc(a.meeting_title||'—')}</td>
          <td><button class="btn btn-danger btn-sm btn-delete-action">×</button></td></tr>`;
      });
      $('#actionsTable').html(html);
    });
  }
  $('.filter-status').on('click', function () { loadActions($(this).data('status')); });
  $(document).on('change', '.status-select', function () {
    const row = $(this).closest('tr');
    $.ajax({
      url: 'api/actions.php?id=' + row.data('id'), method: 'PUT', contentType: 'application/json',
      data: JSON.stringify({
        action_text: row.find('td:eq(0)').text(),
        owner: row.find('td:eq(1)').text(),
        deadline: row.find('td:eq(2)').text() === '—' ? null : row.find('td:eq(2)').text(),
        priority: row.find('.badge').first().text(),
        status: $(this).val()
      })
    });
  });
  $(document).on('click', '.btn-delete-action', function () {
    if (!confirm('Delete?')) return;
    $.ajax({ url: 'api/actions.php?id=' + $(this).closest('tr').data('id'), method: 'DELETE', success: () => loadActions() });
  });

  function esc(t) {
    if (!t) return '';
    return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
});
