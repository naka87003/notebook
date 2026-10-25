import { mount } from '@vue/test-utils';
import { afterEach, describe, expect, test, vi } from 'vitest';
import NoteContent from '../Components/NoteContent.vue';
import { parseNoteContent } from '../Utils/noteContent';

const options = {
  global: {
    stubs: {
      VBtn: { template: '<button @click="$emit(\'click\', $event)"><slot /></button>' },
      VSnackbar: {
        props: ['modelValue', 'color'],
        template: '<div v-if="modelValue"><slot /></div>',
      },
      VIcon: true,
    },
  },
};

afterEach(() => vi.unstubAllGlobals());

describe('parseNoteContent', () => {
  test('keeps ordinary text and blank lines as text', () => {
    expect(parseNoteContent('first\n\nlast')).toEqual([
      { type: 'text', content: 'first', lines: 1 },
      { type: 'text', content: '', lines: 1 },
      { type: 'text', content: 'last', lines: 1 },
    ]);
  });

  test('parses multiple code blocks and preserves indentation and empty lines', () => {
    expect(
      parseNoteContent('before\r\n```js\r\n  x();\r\n\r\n  y();\r\n```\r\n```\r\n<code>\r\n```')
    ).toEqual([
      { type: 'text', content: 'before', lines: 1 },
      { type: 'code', content: '  x();\n\n  y();', language: 'js', lines: 3 },
      { type: 'code', content: '<code>', language: '', lines: 1 },
    ]);
  });

  test('counts only the code content, excluding its fences', () => {
    expect(parseNoteContent('```js\n\n```')).toEqual([
      { type: 'code', content: '', language: 'js', lines: 1 },
    ]);
  });

  test('leaves incomplete and inline fences as ordinary text', () => {
    expect(parseNoteContent('inline ```js\n```js\n  incomplete')).toEqual([
      { type: 'text', content: 'inline ```js', lines: 1 },
      { type: 'text', content: '```js', lines: 1 },
      { type: 'text', content: '  incomplete', lines: 1 },
    ]);
  });
});

describe('NoteContent', () => {
  test('highlights a specified language without treating code as HTML', async () => {
    const content = '```js\nconst message = "<img src=x onerror=alert(1)>";\n```';
    const wrapper = mount(NoteContent, { ...options, props: { content } });

    await vi.waitFor(() => expect(wrapper.find('pre.shiki').exists()).toBe(true));
    expect(wrapper.find('.shiki span[style*="--shiki-light"]').exists()).toBe(true);
    expect(wrapper.find('.shiki span[style*="--shiki-dark"]').exists()).toBe(true);
    expect(wrapper.get('code').text()).toBe('const message = "<img src=x onerror=alert(1)>";');
    expect(wrapper.find('img').exists()).toBe(false);
  });

  test.each(['', 'not-a-language'])('displays code as plain text for language %j', (language) => {
    const wrapper = mount(NoteContent, {
      ...options,
      props: { content: `\`\`\`${language}\n<img src=x onerror=alert(1)>\n\`\`\`` },
    });
    expect(wrapper.get('code').text()).toBe('<img src=x onerror=alert(1)>');
    expect(wrapper.find('code span').exists()).toBe(false);
    expect(wrapper.find('img').exists()).toBe(false);
  });

  test('updates highlighted code when the note changes', async () => {
    const wrapper = mount(NoteContent, {
      ...options,
      props: { content: '```js\nconst first = 1;\n```' },
    });
    await wrapper.setProps({ content: '```python\nsecond = 2\n```' });
    await vi.waitFor(() => expect(wrapper.find('pre.shiki').exists()).toBe(true));
    expect(wrapper.get('code').text()).toBe('second = 2');
  });

  test.each([
    { content: 'one\ntwo\nthree\nfour\nfive\nsix', truncated: false },
    { content: 'one\ntwo\nthree\nfour\nfive\nsix\nseven', truncated: true },
  ])('limits the preview to six lines', ({ content, truncated }) => {
    const wrapper = mount(NoteContent, { ...options, props: { content } });

    expect(wrapper.findAll('.note-paragraph').map((paragraph) => paragraph.text())).toEqual([
      'one',
      'two',
      'three',
      'four',
      'five',
      truncated ? 'six...' : 'six',
    ]);
    expect(wrapper.text().includes('Show more')).toBe(truncated);
  });

  test('shows a complete code block when it fits after the preceding text', () => {
    const content =
      'intro\n```js\none\ntwo\n```\nafter\nlater one\nlater two\nlater three\nlater four\nlater five';
    const wrapper = mount(NoteContent, { ...options, props: { content } });

    expect(wrapper.findAll('.note-paragraph').map((paragraph) => paragraph.text())).toEqual([
      'intro',
      'after',
      'later one',
      'later two...',
    ]);
    expect(wrapper.get('code').text()).toBe('one\ntwo');
    expect(wrapper.text()).not.toContain('later three');
    expect(wrapper.text()).toContain('Show more');
  });

  test('hides a code block that does not fit after the preceding text', () => {
    const content = 'first\nsecond\nthird\nfourth\nfifth\n```js\none\ntwo\nthree\nfour\n```';
    const wrapper = mount(NoteContent, { ...options, props: { content } });

    expect(wrapper.findAll('.note-paragraph').map((paragraph) => paragraph.text())).toEqual([
      'first',
      'second',
      'third',
      'fourth',
      'fifth...',
    ]);
    expect(wrapper.find('code').exists()).toBe(false);
    expect(wrapper.text()).toContain('Show more');
  });

  test('collapses the preview again when the content changes', async () => {
    const wrapper = mount(NoteContent, {
      ...options,
      props: { content: 'one\ntwo\nthree\nfour\nfive\nsix\nseven\neight\nnine' },
    });
    await wrapper.get('button').trigger('click');
    expect(wrapper.findAll('.note-paragraph')).toHaveLength(9);
    expect(wrapper.find('button').exists()).toBe(false);
    expect(wrapper.findAll('.note-paragraph').at(-1)?.text()).toBe('nine');

    await wrapper.setProps({
      content: 'new one\nnew two\nnew three\nnew four\nnew five\nnew six\nnew seven\nnew eight\nnew nine',
    });

    expect(wrapper.findAll('.note-paragraph')).toHaveLength(6);
    expect(wrapper.text()).not.toContain('new seven');
    expect(wrapper.text()).toContain('Show more');
    expect(wrapper.findAll('.note-paragraph').at(-1)?.text()).toBe('new six...');
  });

  test('expands long blocks before offering the full code for copying', async () => {
    const writeText = vi.fn().mockResolvedValue(undefined);
    vi.stubGlobal('navigator', { clipboard: { writeText } });
    const content =
      '```js\n<script>\n  one();\n  two();\n  three();\n  four();\n  five();\n  six();\n  seven();\n</script>\n```';
    const wrapper = mount(NoteContent, { ...options, props: { content } });

    expect(wrapper.find('pre').exists()).toBe(false);
    expect(wrapper.get('.note-paragraph').text()).toBe('...');
    await wrapper.get('button').trigger('click');
    expect(wrapper.get('code').text()).toBe(
      '<script>\n  one();\n  two();\n  three();\n  four();\n  five();\n  six();\n  seven();\n</script>'
    );
    expect(wrapper.find('script').exists()).toBe(false);
    await wrapper.get('.note-code-copy').trigger('click');
    expect(writeText).toHaveBeenCalledWith(
      '<script>\n  one();\n  two();\n  three();\n  four();\n  five();\n  six();\n  seven();\n</script>'
    );
    expect(wrapper.text()).toContain('Copied');
    expect(wrapper.getComponent(options.global.stubs.VSnackbar).props('color')).toBe('success');
  });

  test('reports clipboard errors', async () => {
    vi.stubGlobal('navigator', {
      clipboard: { writeText: vi.fn().mockRejectedValue(new Error('denied')) },
    });
    const wrapper = mount(NoteContent, { ...options, props: { content: '```\nx\n```' } });
    await wrapper.get('.note-code-copy').trigger('click');
    expect(wrapper.text()).toContain('Copy failed');
    expect(wrapper.getComponent(options.global.stubs.VSnackbar).props('color')).toBe('error');
  });
});
