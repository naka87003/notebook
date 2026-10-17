import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, expect, test, vi } from 'vitest';
import { codeToHtml } from 'shiki';
import HighlightedCode from '../Components/HighlightedCode.vue';

vi.mock('shiki', () => ({
  bundledLanguages: { js: true },
  codeToHtml: vi.fn(),
}));

beforeEach(() => vi.resetAllMocks());

test('keeps the latest highlighted code when an older request finishes later', async () => {
  let resolveOld: (html: string) => void;
  const oldResult = new Promise<string>((resolve) => {
    resolveOld = resolve;
  });
  vi.mocked(codeToHtml)
    .mockReturnValueOnce(oldResult)
    .mockResolvedValueOnce('<pre class="shiki"><code>new code</code></pre>');

  const wrapper = mount(HighlightedCode, {
    props: { content: 'old code', language: 'js' },
  });
  await wrapper.setProps({ content: 'new code' });
  await flushPromises();

  expect(wrapper.get('pre.shiki code').text()).toBe('new code');

  resolveOld('<pre class="shiki"><code>old code</code></pre>');
  await flushPromises();

  expect(wrapper.get('pre.shiki code').text()).toBe('new code');
});

test('displays the original code as text if highlighting fails', async () => {
  vi.mocked(codeToHtml).mockRejectedValueOnce(new Error('failed to load'));
  const content = '<img src=x onerror=alert(1)>';
  const wrapper = mount(HighlightedCode, { props: { content, language: 'js' } });

  await flushPromises();

  expect(codeToHtml).toHaveBeenCalledOnce();
  expect(wrapper.get('code').text()).toBe(content);
  expect(wrapper.find('img').exists()).toBe(false);
});
