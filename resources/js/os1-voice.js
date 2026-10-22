/*
 * Voice meter: turns any <audio> element's output into a smoothed 0..1 level.
 * Reused later for the AI's streamed TTS, so the ring "speaks" with any voice.
 */

let context = null;
const sources = new WeakMap();

export function createVoiceMeter(audio, { attack = 0.5, release = 0.08, gain = 4 } = {}) {
    context ??= new (window.AudioContext || window.webkitAudioContext)();

    // createMediaElementSource can only be called once per element
    let source = sources.get(audio);
    if (!source) {
        source = context.createMediaElementSource(audio);
        sources.set(audio, source);
    }

    const analyser = context.createAnalyser();
    analyser.fftSize = 512;
    source.connect(analyser);
    analyser.connect(context.destination);

    const samples = new Uint8Array(analyser.fftSize);
    let smoothed = 0;

    return {
        // Must be called from a user gesture or after play() succeeds, or output stays muted
        resume: () => context.resume(),

        level() {
            analyser.getByteTimeDomainData(samples);

            let sum = 0;
            for (let i = 0; i < samples.length; i++) {
                const value = (samples[i] - 128) / 128;
                sum += value * value;
            }

            const target = Math.min(1, Math.sqrt(sum / samples.length) * gain);
            // Fast attack, slow release: reads as breathing, not flicker
            smoothed += (target - smoothed) * (target > smoothed ? attack : release);

            return smoothed;
        },
    };
}
