/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
enum LogsFields {
  'LOGS' = 'logs',
  'WARNINGS' = 'warnings',
  'ERRORS' = 'errors'
}

enum FeedbackFields {
  'EMAIL' = 'email',
  'COMMENTS' = 'comments'
}

export { LogsFields, FeedbackFields };

type Logs = Partial<Record<LogsFields, string>>;

type Feedback = Partial<Record<FeedbackFields, string>>;

export type { Logs, Feedback };
