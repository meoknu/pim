import {JobStatus} from './JobStatus';

const ITEMS_PER_PAGE = 25;

type JobExecutionFilter = {
  page: number;
  size: number;
  type: string[];
  status: JobStatus[];
};

const getDefaultJobExecutionFilter = () => ({
  page: 1,
  size: ITEMS_PER_PAGE,
  type: [],
  status: [],
});

const isDefaultJobExecutionFilter = ({page, size, type, status}: JobExecutionFilter): boolean =>
  1 === page && ITEMS_PER_PAGE === size && 0 === status.length && 0 === type.length;

export type {JobExecutionFilter};
export {getDefaultJobExecutionFilter, isDefaultJobExecutionFilter};
