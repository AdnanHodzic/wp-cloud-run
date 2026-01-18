import { cn } from '@/lib/utils';

type TransitionInOutProps = {
	children: React.ReactNode;
  from?: 'left' | 'right' | 'top' | 'bottom' | 'fade';
	className?: string;
}

const TransitionWrapper = ({ children, from = 'bottom', className }: TransitionInOutProps) => {

  const classMap = {
    left: 'starting:-translate-x-2',
    right: 'starting:translate-x-2',
    top: 'starting:-translate-y-2',
    bottom: 'starting:translate-y-2',
    fade: 'starting:opacity-0',
  }

  const classes = cn('starting:opacity-0 duration-300 transition-all',
    'delay-100 ease-out',
    classMap[from], className);

  return (
    <div className={classes}>
      {children}
    </div>
  );
};

export default TransitionWrapper;