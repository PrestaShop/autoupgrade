/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import type { Step } from '../types/Stepper';

export default class Stepper {
  #baseClass = 'stepper__step';
  #currentClass = `${this.#baseClass}--current`;
  #doneClass = `${this.#baseClass}--done`;
  #normalClass = `${this.#baseClass}--normal`;
  #stepperHydrationClass = 'stepper--hydration';

  /**
   * @public
   * @param {string} currentStep - The code of the current step to be set.
   * @description Sets the current step in the stepper and updates the classes for each step accordingly.
   */
  public setCurrentStep = (currentStep: string) => {
    const stepIndex = this.#getStepIndex(currentStep);

    if (stepIndex === -1) {
      console.debug(`Step ${currentStep} not found in list.`);
      return;
    }

    this.#stepper?.classList.add(this.#stepperHydrationClass);

    this.#steps.forEach((step, i) => {
      const { element } = step;

      const newClass = this.#getClassOfStep(stepIndex, i);

      if (!element.classList.contains(newClass)) {
        element.classList.remove(this.#currentClass, this.#doneClass, this.#normalClass);
        element.classList.add(newClass);
      }
    });
  };

  #getStepIndex = (stepToGetIndex: string): number => {
    return this.#steps.findIndex((step) => step.code === stepToGetIndex);
  };

  #getClassOfStep(referenceIndex: number, currentIndex: number): string {
    if (currentIndex === referenceIndex) {
      return this.#currentClass;
    }

    if (currentIndex < referenceIndex) {
      return this.#doneClass;
    }

    return this.#normalClass;
  }

  get #stepper(): HTMLDivElement {
    const stepper = document.getElementById(
      window.AutoUpgradeVariables.stepper_parent_id
    ) as HTMLDivElement | null;
    if (!stepper) {
      throw new Error("The stepper wasn't found inside DOM. stepper can't be initiated properly");
    }
    return stepper;
  }

  get #steps(): Step[] {
    const domSteps = Array.from(this.#stepper.children) as HTMLElement[];

    if (!domSteps.length) {
      throw new Error("The stepper hasn't steps inside DOM. stepper can't be initiated properly");
    }

    return domSteps.map((step) => {
      const stepCode = step.dataset.stepCode;
      if (!stepCode) {
        throw new Error(
          "Step code is missing in one of the steps. stepper can't be initiated properly"
        );
      }
      return {
        code: stepCode,
        element: step
      };
    });
  }
}
