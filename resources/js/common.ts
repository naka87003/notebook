import dayjs, { extend } from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

extend(relativeTime);

export const simplifyDateTime = (str: string): string => dayjs(str).format('YYYY/MM/DD HH:mm');
export const splitByNewline = (text: string): string[] => text.split(/\r?\n/);

export const relativeDateTime = (str: string): string => {
  const now = dayjs();
  const updateDateTime = dayjs(str);
  const diff = now.diff(updateDateTime);
  if (diff < 6.048e8) {
    return updateDateTime.fromNow();
  } else {
    return updateDateTime.format('YYYY/MM/DD');
  }
};
