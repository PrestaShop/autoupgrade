import Stepper from '../../src/ts/utils/Stepper';

const createMockStepperHTML = () => {
  document.body.innerHTML = `
    <div class="stepper" id="stepper_content">
      <div class="stepper__step stepper__step--current  stepper__step--first" data-step-code="version-choice">
      </div>
      <div class="stepper__step stepper__step--normal" data-step-code="update-options">
      </div>
      <div class="stepper__step stepper__step--normal" data-step-code="backup">
      </div>
      <div class="stepper__step stepper__step--normal" data-step-code="update">
      </div>
      <div class="stepper__step stepper__step--normal stepper__step--last" data-step-code="post-update">
      </div>
    </div>
  `;
};

describe('Stepper', () => {
  beforeEach(() => {
    createMockStepperHTML();
  });

  it('should initialize stepper with all steps', () => {
    const stepper = new Stepper();

    expect(stepper['steps'].length).toBe(5);
    expect(stepper['steps'][0].code).toBe('version-choice');
    expect(stepper['steps'][1].code).toBe('update-options');
    expect(stepper['steps'][2].code).toBe('backup');
    expect(stepper['steps'][3].code).toBe('update');
    expect(stepper['steps'][4].code).toBe('post-update');
  });

  it('should throw an error if the stepper is not found in the DOM', () => {
    document.body.innerHTML = '';

    expect(() => new Stepper()).toThrow(
      "The stepper wasn't found inside DOM. stepper can't be initiated properly"
    );
  });

  it('should throw an error if the stepper contains no steps', () => {
    document.body.innerHTML = '<div class="stepper" id="stepper_content"></div>';

    expect(() => new Stepper()).toThrow(
      "The stepper hasn't steps inside DOM. stepper can't be initiated properly"
    );
  });

  it('should throw an error if a step is missing the step code', () => {
    document.querySelector('[data-step-code="backup"]')?.removeAttribute('data-step-code');

    expect(() => new Stepper()).toThrow(
      "Step code is missing in one of the steps. stepper can't be initiated properly"
    );
  });

  it('should add class to stepper parent then using setCurrentStep method', () => {
    const stepperParent = document.getElementById('stepper_content');

    expect(stepperParent!.classList.contains('stepper--hydration')).toBe(false);

    new Stepper().setCurrentStep('update-options');

    expect(stepperParent!.classList.contains('stepper--hydration')).toBe(true);
  });

  it('should mark all previous steps as done and the current one as current', () => {
    const stepper = new Stepper();

    const versionChoiceStep = document.querySelector('[data-step-code="version-choice"]');
    const updateOptionsStep = document.querySelector('[data-step-code="update-options"]');
    const backupStep = document.querySelector('[data-step-code="backup"]');
    const updateStep = document.querySelector('[data-step-code="update"]');
    const postUpdateStep = document.querySelector('[data-step-code="post-update"]');

    stepper.setCurrentStep('version-choice');

    expect(versionChoiceStep?.classList.contains('stepper__step--done')).toBe(false);
    expect(updateOptionsStep?.classList.contains('stepper__step--done')).toBe(false);
    expect(backupStep?.classList.contains('stepper__step--done')).toBe(false);
    expect(updateStep?.classList.contains('stepper__step--done')).toBe(false);
    expect(postUpdateStep?.classList.contains('stepper__step--done')).toBe(false);

    expect(versionChoiceStep?.classList.contains('stepper__step--current')).toBe(true);
    expect(updateOptionsStep?.classList.contains('stepper__step--current')).toBe(false);
    expect(backupStep?.classList.contains('stepper__step--current')).toBe(false);
    expect(updateStep?.classList.contains('stepper__step--current')).toBe(false);
    expect(postUpdateStep?.classList.contains('stepper__step--current')).toBe(false);

    expect(versionChoiceStep?.classList.contains('stepper__step--normal')).toBe(false);
    expect(updateOptionsStep?.classList.contains('stepper__step--normal')).toBe(true);
    expect(backupStep?.classList.contains('stepper__step--normal')).toBe(true);
    expect(updateStep?.classList.contains('stepper__step--normal')).toBe(true);
    expect(postUpdateStep?.classList.contains('stepper__step--normal')).toBe(true);

    stepper.setCurrentStep('update');

    expect(versionChoiceStep?.classList.contains('stepper__step--done')).toBe(true);
    expect(updateOptionsStep?.classList.contains('stepper__step--done')).toBe(true);
    expect(backupStep?.classList.contains('stepper__step--done')).toBe(true);
    expect(updateStep?.classList.contains('stepper__step--done')).toBe(false);
    expect(postUpdateStep?.classList.contains('stepper__step--done')).toBe(false);

    expect(versionChoiceStep?.classList.contains('stepper__step--current')).toBe(false);
    expect(updateOptionsStep?.classList.contains('stepper__step--current')).toBe(false);
    expect(backupStep?.classList.contains('stepper__step--current')).toBe(false);
    expect(updateStep?.classList.contains('stepper__step--current')).toBe(true);
    expect(postUpdateStep?.classList.contains('stepper__step--current')).toBe(false);

    expect(versionChoiceStep?.classList.contains('stepper__step--normal')).toBe(false);
    expect(updateOptionsStep?.classList.contains('stepper__step--normal')).toBe(false);
    expect(backupStep?.classList.contains('stepper__step--normal')).toBe(false);
    expect(updateStep?.classList.contains('stepper__step--normal')).toBe(false);
    expect(postUpdateStep?.classList.contains('stepper__step--normal')).toBe(true);
  });
});
