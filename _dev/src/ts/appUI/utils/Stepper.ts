/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
import type { Step } from '../types/Stepper';

export default class Stepper {
  #stepper: HTMLDivElement | null = null;
  #steps: Step[] = [];

  #baseClass = 'stepper__step';
  #currentClass = `${this.#baseClass}--current`;
  #doneClass = `${this.#baseClass}--done`;
  #normalClass = `${this.#baseClass}--normal`;
  #stepperHydrationClass = 'stepper--hydration';

  constructor() {
    this.#initStepper();
  }

  /**
   * @private
   * @throws Error Will throw an error if the stepper or its steps are not found in the DOM.
   * @description Initializes the Stepper by finding the parent element in the DOM and setting up the steps.
   */
  #initStepper = (): void => {
    this.#stepper = document.getElementById(
      window.AutoUpgradeVariables.stepper_parent_id
    ) as HTMLDivElement | null;
    if (!this.#stepper) {
      throw new Error("The stepper wasn't found inside DOM. stepper can't be initiated properly");
    }

    const domSteps = Array.from(this.#stepper.children) as HTMLElement[];

    if (!domSteps.length) {
      throw new Error("The stepper hasn't steps inside DOM. stepper can't be initiated properly");
    }

    this.#steps = domSteps.map((step) => {
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
  };

  /**
   * @public
   * @param {string} currentStep - The code of the current step to be set.
   * @description Sets the current step in the stepper and updates the classes for each step accordingly.
   */
  public setCurrentStep = (currentStep: string) => {
    if (this.#getStepIndex(currentStep) === -1) {
      this.#initStepper();
    }

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
}
