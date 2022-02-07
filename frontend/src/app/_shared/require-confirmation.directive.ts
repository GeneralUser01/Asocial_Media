import { Directive, Input } from '@angular/core';
import { AbstractControl, NG_VALIDATORS, ValidationErrors, Validator, ValidatorFn } from '@angular/forms';

// This code was inspired by:
// - https://angular.io/guide/form-validation#adding-cross-validation-to-template-driven-forms
// - https://angular.io/guide/form-validation#adding-custom-validators-to-template-driven-forms
// - https://tipsfordev.com/best-way-to-implement-angular-cross-field-validation
// - https://blog.dmbcllc.com/angular-cross-field-validation/
// - https://stackoverflow.com/questions/43553544/how-can-i-manually-set-an-angular-form-field-as-invalid
// - https://angular.io/api/forms/AbstractControl

export function requireConfirmationValidator(fieldThatRequiresConfirmation: string): ValidatorFn {
  return (control: AbstractControl): ValidationErrors | null => {
    const actual = control.get(fieldThatRequiresConfirmation);
    const confirm = control.get(fieldThatRequiresConfirmation + 'Confirmation');

    const isValid = actual && confirm && actual.value === confirm.value;

    const errorKey = 'requireConfirmation';
    if (confirm) {
      if (isValid) {
        if (confirm.errors && (errorKey in confirm.errors)) {
          // The "confirm" field still has the manually set error. We need to
          // revalidate it to clear that error. If the error was fixed because
          // the "confirm" field was changed then we don't need to do this but
          // it could be that the error was fixed because the "actual" field was
          // changed in which case the "confirm" field's errors wouldn't be
          // automatically cleared.
          confirm.updateValueAndValidity();
        }
      } else {
        // Manually add our errors to this form control:
        confirm.setErrors({ [errorKey]: true });
      }
    }

    return isValid ? null : { [fieldThatRequiresConfirmation + 'RequireConfirmation']: true };
  };
}

@Directive({
  selector: '[appRequireConfirmation]',
  providers: [{ provide: NG_VALIDATORS, useExisting: RequireConfirmationValidatorDirective, multi: true }]
})
export class RequireConfirmationValidatorDirective implements Validator {
  /** Names of form controls that also have a confirmation control. It is
   * assumed that the confirmation control has the same name but suffixed with
   * `Confirmation`. Multiple controls can be specified by separating the names
   * with commas (`,`).
   */
  @Input('appRequireConfirmation') requireConfirmation = '';

  constructor() { }

  validate(control: AbstractControl): ValidationErrors | null {
    if (!this.requireConfirmation) return null;
    const fieldNames = this.requireConfirmation.split(',');

    let errors: ValidationErrors | null = null;
    for (const fieldName of fieldNames) {
      const error = requireConfirmationValidator(fieldName)(control);
      if (error) {
        if (!errors) {
          errors = error;
        } else {
          // Append the new error to the existing errors:
          Object.assign(errors, error);
        }
      }
    }
    return errors;
  }
}
