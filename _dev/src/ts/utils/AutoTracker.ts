import DomLifecycle from '../types/DomLifecycle';
import analytics from '../api/SegmentApi';

export default class AutoTracker implements DomLifecycle {
  container: HTMLElement;
  listeners: Map<HTMLElement, EventListener> = new Map();

  constructor(container: HTMLElement) {
    this.container = container;
  }

  mount(): void {
    const elementsToTrack: NodeListOf<HTMLElement> =
      this.container.querySelectorAll('[data-au-tracking]');

    elementsToTrack.forEach((element) => {
      const listener = () => this.#trackElement(element);
      element.addEventListener('click', listener);
      this.listeners.set(element, listener);
    });
  }

  readonly #trackElement = (element: HTMLElement) => {
    const eventToSubmit = element.dataset.auTracking;
    analytics.track(`[SUE] ${eventToSubmit}`);
  };

  beforeDestroy(): void {
    this.listeners.forEach((listener, element) => {
      element.removeEventListener('click', listener);
    });
    this.listeners.clear();
  }
}
