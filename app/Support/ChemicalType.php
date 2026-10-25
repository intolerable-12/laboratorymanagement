<?php

namespace App\Support;

final class ChemicalType
{
    public const ORGANIC = 'Organic';
    public const INORGANIC = 'Inorganic';

    public static function values(): array
    {
        return [self::ORGANIC, self::INORGANIC];
    }

    /**
     * Names that are known organic compounds or organic mixtures in the
     * inventory seed data. Unknown names intentionally fall back to
     * Inorganic so the migration can complete without leaving null types.
     */
    public static function organicNames(): array
    {
        return [
            'trichloroacetic acid', 'acetic acid', 'glacial acetic acid',
            'citric acid', 'anhydrous citric acid', 'salicylic acid',
            'methanol', 'ethanol', 'acetone', 'propanol', 'pentanol',
            'n-heptane', 'amyl acetate', 'methyl salicylate', 'nitrobenzene',
            'methylene chloride', 'aniline', 'propylene glycol', 'glycerol',
            'polyethylene glycol', 'potassium bitartrate', 'lead acetate',
            'cupric acetate', 'sodium tartrate', 'potassium oxalate',
            'barium acetate', 'potassium hydrogen phthalate', 'potassium acetate',
            'blue litmus paper', 'red litmus paper', 'phenolphthalein',
            'methyl red', 'methyl orange', 'thymol blue', 'bromophenol blue',
            'crystal violet', 'carmine dye', 'eriochrome black',
            'nutrient agar', 'dextrose agar granulated', 'starch', 'yeast',
            'corn starch', 'pancreatin solution', 'glucose', 'pepsin solution',
            'methylene blue', 'sudan iii', 'formalin', 'benzamide', '1-naphthol',
            'beta-naphthol purified', 'stearic acid', 'tannic acid', 'molasses',
            'phthalic anhydride', 'nitroso naphthol', 'sucrose',
            'thymol powder', 'paraffin wax', 'sodium lauryl sulfate',
            'menthol crystal', 'propyl paraben', 'methyl paraben', 'cetyl alcohol',
            'lanolin', 'camphor', 'pyrogallol solution', 'molisch reagent', 'tween',
            'alpha-naphthol solution', 'phloroglucinol', 'olive oil',
            'eucalyptus oil', 'honey', 'virgin oil', 'beeswax', 'acacia/gum arabic',
            'hydrous wool fat', 'liquid paraffin', 'whitewax', 'lemon scent',
            'peppermint oil', 'lavender oil', 'phenal glycerin', 'phenol',
            "benedict's solution", "barfoed's reagent", 'powder soap (disinfectant)',
        ];
    }

    public static function fromName(?string $name): string
    {
        $normalized = mb_strtolower(trim((string) $name));

        return in_array($normalized, self::organicNames(), true)
            ? self::ORGANIC
            : self::INORGANIC;
    }
}
