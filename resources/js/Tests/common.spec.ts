import { describe, expect, test } from 'vitest';
import { simplifyDateTime, splitByNewline } from '../common.js';

describe('simplifyDateTime 関数のテスト', () => {
  test('ISO形式の日付文字列を「YYYY/MM/DD HH:mm」形式に変換できる', () => {
    const input = '2025-07-21T15:30:00Z';
    const result = simplifyDateTime(input);
    expect(result).toBe('2025/07/22 00:30');
  });

  test('年月日だけの日付文字列も正しく変換できる', () => {
    const input = '2025-07-21';
    const result = simplifyDateTime(input);
    expect(result).toBe('2025/07/21 00:00');
  });

  test('無効な日付文字列は "Invalid Date" を返す', () => {
    const input = 'not-a-date';
    const result = simplifyDateTime(input);
    expect(result).toBe('Invalid Date');
  });
});

describe('splitByNewline', () => {
  test('LF（\\n）で区切られた文字列を正しく分割できる', () => {
    const input = '行1\n行2\n行3';
    const result = splitByNewline(input);
    expect(result).toEqual(['行1', '行2', '行3']);
  });

  test('CRLF（\\r\\n）で区切られた文字列を正しく分割できる', () => {
    const input = '行1\r\n行2\r\n行3';
    const result = splitByNewline(input);
    expect(result).toEqual(['行1', '行2', '行3']);
  });

  test('改行がない文字列はそのまま1行として扱われる', () => {
    const input = '行1';
    const result = splitByNewline(input);
    expect(result).toEqual(['行1']);
  });

  test('空文字列は空の1行として扱われる', () => {
    const input = '';
    const result = splitByNewline(input);
    expect(result).toEqual(['']);
  });
});
