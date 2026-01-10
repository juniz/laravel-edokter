import * as React from 'react';

interface RadioGroupContextValue {
  name: string;
  value?: string;
  onValueChange?: (value: string) => void;
}

const RadioGroupContext = React.createContext<RadioGroupContextValue>({
  name: 'radio-group',
});

interface RadioGroupProps extends React.HTMLAttributes<HTMLDivElement> {
  value?: string;
  onValueChange?: (value: string) => void;
  name?: string;
}

const RadioGroup = React.forwardRef<HTMLDivElement, RadioGroupProps>(
  ({ className, value, onValueChange, name = `radio-group-${Math.random().toString(36).substr(2, 9)}`, children, ...props }, ref) => {
    return (
      <RadioGroupContext.Provider value={{ name, value, onValueChange }}>
      <div ref={ref} className={className} role="radiogroup" {...props}>
          {children}
      </div>
      </RadioGroupContext.Provider>
    );
  }
);
RadioGroup.displayName = 'RadioGroup';

interface RadioGroupItemProps extends React.InputHTMLAttributes<HTMLInputElement> {
  value: string;
  checked?: boolean;
  onCheckedChange?: () => void;
  name?: string;
}

const RadioGroupItem = React.forwardRef<HTMLInputElement, RadioGroupItemProps>(
  ({ className, value, checked, onCheckedChange, id, name, ...props }, ref) => {
    const context = React.useContext(RadioGroupContext);
    const inputId = id || `radio-${value}`;
    const radioName = name || context.name;
    const isChecked = checked !== undefined ? checked : context.value === value;
    const handleChange = onCheckedChange || (() => context.onValueChange?.(value));
    
    return (
      <input
        ref={ref}
        type="radio"
        id={inputId}
        name={radioName}
        value={value}
        checked={isChecked}
        onChange={handleChange}
        className={`h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500 ${className || ''}`}
        {...props}
      />
    );
  }
);
RadioGroupItem.displayName = 'RadioGroupItem';

export { RadioGroup, RadioGroupItem };

