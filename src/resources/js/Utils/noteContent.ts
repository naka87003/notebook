export type NoteContentBlock =
  | { type: 'text'; content: string; lines: number }
  | { type: 'code'; content: string; language: string; lines: number };

// Only complete, standalone fences are interpreted. Unclosed fences remain text.
export function parseNoteContent(content: string): NoteContentBlock[] {
  const lines = content.split(/\r?\n/);
  const blocks: NoteContentBlock[] = [];

  for (let i = 0; i < lines.length; i++) {
    const opening = lines[i].match(/^```([^`]*)\s*$/);
    if (opening) {
      let end = i + 1;
      while (end < lines.length && !/^```\s*$/.test(lines[end])) end++;
      if (end < lines.length) {
        blocks.push({
          type: 'code',
          content: lines.slice(i + 1, end).join('\n'),
          language: opening[1].trim(),
          lines: end - i + 1,
        });
        i = end;
        continue;
      }
    }
    blocks.push({ type: 'text', content: lines[i], lines: 1 });
  }
  return blocks;
}
