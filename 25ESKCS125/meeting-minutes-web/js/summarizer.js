/**
 * Built-in Meeting Minutes Summarizer
 * Works offline — no API key required.
 */
function summarizeMeetingNotes(raw, meta) {
  meta = meta || {};
  const text = (raw || '').replace(/\r\n/g, '\n').trim();
  if (!text) return null;

  const lines = text.split('\n').map(l => l.trim()).filter(Boolean);

  function pick(patterns) {
    for (const line of lines) {
      for (const p of patterns) {
        const m = line.match(p);
        if (m) return (m[1] || m[0]).trim();
      }
    }
    return '';
  }

  const title = meta.title || pick([
    /^(?:meeting|title)\s*[:\-]\s*(.+)/i,
    /^#\s+(.+)/
  ]) || 'Untitled Meeting';

  const date = meta.date || pick([
    /^(?:date)\s*[:\-]\s*(.+)/i
  ]);

  const organizer = meta.organizer || pick([
    /^(?:organizer|chair|hosted by)\s*[:\-]\s*(.+)/i
  ]);

  let attendees = meta.attendees || pick([
    /^(?:attendees|participants|present)\s*[:\-]\s*(.+)/i
  ]);

  const speakers = [];
  const speakerRe = /^([A-Z][a-zA-Z .'-]{1,30})\s*[:\-]\s+(.+)/;
  const bodyLines = [];
  for (const line of lines) {
    const sm = line.match(speakerRe);
    if (sm) {
      const name = sm[1].trim();
      if (!speakers.includes(name)) speakers.push(name);
      bodyLines.push(sm[2].trim());
    } else {
      bodyLines.push(line);
    }
  }
  if (!attendees && speakers.length) attendees = speakers.join(', ');

  const actionItems = [];
  const decisions = [];
  const questions = [];
  const nextSteps = [];
  const discussion = [];

  const actionRe = /\b(i('ll| will)|we('ll| will)|can you|please|need to|should|action|todo|follow[- ]?up|i can own|i'll take|i will take|targeting|by (eod|friday|monday|tuesday|wednesday|thursday|end of))\b/i;
  const decisionRe = /\b(decid(e|ed|ing)|proposal|we('re| are) going with|lock(ed)?|adopt|ship|depriorit|move .* to q[1-4]|agreed|approval)\b/i;
  const questionRe = /\?|open question|still undecided|parking lot/i;
  const nextRe = /\b(next meeting|next sync|tomorrow eod|follow[- ]?up)\b/i;

  function ownerFrom(s) {
    const m1 = s.match(/\b(?:i('ll| will)|i can own)\b/i);
    // speaker context is lost per-line; try "X will"
    const m2 = s.match(/^([A-Z][a-z]+)\s+(will|can own|to)/);
    if (m2) return m2[1];
    const m3 = s.match(/\b([A-Z][a-z]+)\s+will\b/);
    if (m3) return m3[1];
    return 'Unassigned';
  }

  function deadlineFrom(s) {
    const m = s.match(/\b(by\s+)?((next\s+)?(monday|tuesday|wednesday|thursday|friday|saturday|sunday)|eod|end of (month|week|day)|august\s+\d+|july\s+\d+|20\d{2}-\d{2}-\d{2}|aug(ust)?\s+\d{1,2})\b/i);
    return m ? m[0].replace(/^by\s+/i, '') : '';
  }

  function priorityFrom(s) {
    if (/\b(asap|critical|urgent|high|mvp|ship)\b/i.test(s)) return 'High';
    if (/\b(low|later|nice to have)\b/i.test(s)) return 'Low';
    return 'Medium';
  }

  // Reconstruct speaker-aware lines
  const fullLines = [];
  for (const line of lines) {
    const sm = line.match(speakerRe);
    if (sm) fullLines.push({ speaker: sm[1].trim(), text: sm[2].trim(), raw: line });
    else fullLines.push({ speaker: '', text: line, raw: line });
  }

  for (const item of fullLines) {
    const s = item.text;
    if (/^(meeting|date|attendees|title|organizer)\s*[:\-]/i.test(s)) continue;

    if (questionRe.test(s)) {
      questions.push(s);
      continue;
    }
    if (nextRe.test(s) && !actionRe.test(s)) {
      nextSteps.push(s);
    }
    if (decisionRe.test(s)) {
      decisions.push(s);
    }
    if (actionRe.test(s)) {
      let owner = item.speaker || ownerFrom(s);
      if (/^i\b/i.test(s) && item.speaker) owner = item.speaker;
      actionItems.push({
        action_text: s,
        owner: owner || 'Unassigned',
        deadline: deadlineFrom(s) || null,
        priority: priorityFrom(s),
        status: 'Open'
      });
    }
    if (s.length > 20) discussion.push(s);
  }

  // Dedup discussion that already became actions/decisions
  const used = new Set([...actionItems.map(a => a.action_text), ...decisions, ...questions]);
  const discussionClean = discussion.filter(d => !used.has(d)).slice(0, 8);

  const execParts = [];
  execParts.push(`Meeting "${title}"` + (date ? ` on ${date}` : '') + (attendees ? ` with ${attendees}` : '') + '.');
  if (decisions.length) execParts.push(decisions.length + ' decision(s) recorded.');
  if (actionItems.length) execParts.push(actionItems.length + ' action item(s) assigned.');
  if (!decisions.length && !actionItems.length) execParts.push('No formal decisions or action items were clearly marked in the notes.');

  return {
    title,
    date,
    organizer,
    attendees,
    executive_summary: execParts.join(' '),
    discussion_points: discussionClean.map(d => '- ' + d).join('\n'),
    decisions: decisions.length ? decisions.map((d, i) => (i + 1) + '. ' + d).join('\n') : 'No formal decisions were recorded.',
    open_questions: questions.length ? questions.map(q => '- ' + q).join('\n') : '',
    next_steps: nextSteps.length ? nextSteps.map(n => '- ' + n).join('\n') : '',
    action_items: actionItems
  };
}

function formatMinutesMarkdown(s) {
  let md = `# Meeting Minutes: ${s.title}\n\n`;
  md += `**Date:** ${s.date || '[Not specified]'}  \n`;
  md += `**Organizer:** ${s.organizer || '[Not specified]'}  \n`;
  md += `**Attendees:** ${s.attendees || '[Not specified]'}\n\n`;
  md += `## Executive Summary\n${s.executive_summary}\n\n`;
  md += `## Key Discussion Points\n${s.discussion_points || '- [None extracted]'}\n\n`;
  md += `## Decisions\n${s.decisions}\n\n`;
  md += `## Action Items\n\n| # | Action Item | Owner | Deadline | Priority | Status |\n|---|-------------|-------|----------|----------|--------|\n`;
  if (s.action_items && s.action_items.length) {
    s.action_items.forEach((a, i) => {
      md += `| ${i + 1} | ${a.action_text} | ${a.owner} | ${a.deadline || '—'} | ${a.priority} | ${a.status} |\n`;
    });
  } else {
    md += `| 1 | [None extracted] | — | — | — | — |\n`;
  }
  md += `\n## Open Questions / Parking Lot\n${s.open_questions || '- [None]'}\n\n`;
  md += `## Next Steps & Follow-up\n${s.next_steps || '- [Not specified]'}\n`;
  return md;
}
