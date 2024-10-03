import type { Step } from '../types/Stepper';

export default class Stepper {
  private stepper: HTMLDivElement;
  private steps: Step[];

  private stepClasses = {
    current: 'stepper__step--current',
    done: 'stepper__step--done'
  };

  constructor() {
    const stepper = document.getElementById(
      window.AutoUpgradeVariables.stepper_parent_id
    ) as HTMLDivElement | null;
    if (stepper) {
      this.stepper = stepper;
    } else {
      throw new Error("The stepper wasn't found inside DOM. stepper can't be initiated properly");
    }

    const domSteps = Array.from(this.stepper.children) as HTMLElement[];
    if (!domSteps.length) {
      throw new Error("The stepper hasn't steps inside DOM. stepper can't be initiated properly");
    }

    this.steps = [];

    domSteps.forEach((step) => {
      const stepCode = step.dataset.stepCode;
      if (stepCode) {
        this.steps.push({
          code: stepCode,
          element: step
        });
      } else {
        throw new Error(
          "Step code is missing in one of the steps. stepper can't be initiated properly"
        );
      }
    });
  }

  public setCurrentStep = (currentStep: string) => {
    let foundCurrentStep = false;
    this.steps.forEach((step) => {
      step.element.classList.remove(this.stepClasses.current);
      step.element.classList.remove(this.stepClasses.done);
      if (step.code === currentStep) {
        step.element.classList.add(this.stepClasses.current);
        foundCurrentStep = true;
      } else {
        if (!foundCurrentStep) {
          step.element.classList.add(this.stepClasses.done);
        }
      }
    });
  };
}
