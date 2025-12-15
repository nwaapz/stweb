#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Repair corrupted files where 'on' was replaced by 'در' inside HTML tags/attributes.
"""
import os
import re

BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"

# Common corrupted patterns
REPAIRS = {
    'buttدر': 'button',
    'actiدر': 'action',
    'cدرtainer': 'container',
    'cدرtent': 'content',
    'cدرtact': 'contact',
    'optiدر': 'option',
    'sectiدر': 'section',
    'icدر': 'icon',
    'locatiدر': 'location',
    'collectiدر': 'collection',
    'navigatiدر': 'navigation',
    'paginatiدر': 'pagination',
    'descriptiدر': 'description',
    'specificatiدر': 'specification',
    'conditiدر': 'condition',
    'relatiدر': 'relation',
    'positiدر': 'position',
    'transitiدر': 'transition',
    'animatiدر': 'animation',
    'decoratiدر': 'decoration',
    'operatiدر': 'operation',
    'directiدر': 'direction',
    'selectiدر': 'selection',
    'informatiدر': 'information',
    'phدرe': 'phone',
    'nدرe': 'none',
    'secدرdary': 'secondary',
    'Mدر': 'Mon', # Monday
    'commدر': 'common',
    'favicدر': 'favicon',
    'fدرts': 'fonts',
    'fدرt': 'font',
    'telephدرe': 'telephone',
    'detectiدر': 'detection',
    'cدرfig': 'config',
    'functiدر': 'function',
    'dدرut': 'donut',
    'compدرents': 'components',
    'versiدر': 'version',
    'regiدر': 'region',
    'sessigaدر': 'session', # unlikely but possible
    'subscriptiدر': 'subscription',
    'questiدر': 'question',
    'suggesstiدر': 'suggestion',
    'persدر': 'person',
    'reasدر': 'reason',
    'seasدر': 'season',
    'lesدر': 'lesson',
    'prisدر': 'prison',
    'uniدر': 'union',
    'millidدر': 'million', # unlikely
    'billiدر': 'billion',
    'natدر': 'nation',
    'emotiدر': 'emotion',
    'motiدر': 'motion',
    'solutiدر': 'solution',
    'cauciدر': 'caution',
    'statدر': 'station',
    'mentiدر': 'mention',
    'creatiدر': 'creation',
    'duratiدر': 'duration',
    'evolutiدر': 'evolution',
    'revolutiدر': 'revolution',
    'resolutiدر': 'resolution',
    'pollutiدر': 'pollution',
    'attentiدر': 'attention',
    'intentiدر': 'intention',
    'retentiدر': 'retention',
    'detentiدر': 'detention',
    'conventiدر': 'convention',
    'inventiدر': 'invention',
    'interventiدر': 'intervention',
    'preventiدر': 'prevention',
    'dimensiدر': 'dimension',
    'extensiدر': 'extension',
    'suspensiدر': 'suspension',
    'tensiدر': 'tension',
    'pensiدر': 'pension',
    'missiدر': 'mission',
    'commissiدر': 'commission',
    'permissiدر': 'permission',
    'admissiدر': 'admission',
    'submissiدر': 'submission',
    'transmissiدر': 'transmission',
    'expressiدر': 'expression',
    'impressiدر': 'impression',
    'compressiدر': 'compression',
    'depressiدر': 'depression',
    'oppressiدر': 'oppression',
    'suppressiدر': 'suppression',
    'sessدر': 'session',
    'passiدر': 'passion',
    'fusiدر': 'fusion',
    'confusiدر': 'confusion',
    'diffusiدر': 'diffusion',
    'infusiدر': 'infusion',
    'perfusiدر': 'perfusion',
    'transfusiدر': 'transfusion',
    'visدر': 'vision',
    'revisدر': 'revision',
    'divisدر': 'division',
    'provisدر': 'provision',
    'supervisدر': 'supervision',
    'televisدر': 'television',
    'decisدر': 'decision',
    'precisدر': 'precision',
    'incisدر': 'incision',
    'excisدر': 'excision',
    'circumcisدر': 'circumcision',
    'collisدر': 'collision',
    'explosدر': 'explosion',
    'corrosiدر': 'corrosion',
    'erosiدر': 'erosion',
    'invasiدر': 'invasion',
    'evasiدر': 'evasion',
    'persuasiدر': 'persuasion',
    'occasiدر': 'occasion',
    'conclusiدر': 'conclusion',
    'inclusiدر': 'inclusion',
    'exclusiدر': 'exclusion',
    'illusدر': 'illusion',
    'delusiدر': 'delusion',
    'allusدر': 'allusion',
    'collusiدر': 'collusion',
    'versدر': 'version',
    'diversدر': 'diversion',
    'conversدر': 'conversion',
    'inversدر': 'inversion',
    'reversدر': 'reversion',
    'immersدر': 'immersion',
    'submersدر': 'submersion',
    'aspersدر': 'aspersion',
    'dispersدر': 'dispersion',
    'excursiدر': 'excursion',
    'incursiدر': 'incursion',
    'recursiدر': 'recursion',
    'torsiدر': 'torsion',
    'distorsiدر': 'distorsion',
    'contorsiدر': 'contorsion',
    'cدر': 'con', # Generic catch-all for 'con' prefix might be dangerous, stick to specific words
    'jsoدر': 'json',
    'pythدر': 'python',
    'commدر': 'common',
    'daemدر': 'daemon',
    'lemدر': 'lemon',
    'melدر': 'melon',
    'canدر': 'canon',
    'wagدر': 'wagon',
    'dragدر': 'dragon',
    'flagدر': 'flagon',
    'salmدر': 'salmon',
    'sermدر': 'sermon',
    'demدر': 'demon',
    'harmoدر': 'harmon', # harmony
    'hormoدر': 'hormon', # hormone
    'patrدر': 'patron',
    'matrدر': 'matron',
    'electrدر': 'electron',
    'neutrدر': 'neutron',
    'protدر': 'proton',
    'barدر': 'baron',
    'morدر': 'moron',
    'macarدر': 'macaron',
    'irدر': 'iron',
    'envirدر': 'environ',
    'aprdدر': 'apron',
    'cautidدر': 'caution',
    # Attributes/Tags
    'type="buttدر"': 'type="button"',
    'class="buttدر"': 'class="button"',
    'buttدر>': 'button>',
    '</buttدر>': '</button>',
    'role="navigatiدر"': 'role="navigation"',
    'aria-descriptiدر': 'aria-description',
    'data-actiدر': 'data-action',
}

def repair_files(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        original = content
        
        # Apply specific repairs
        for bad, good in REPAIRS.items():
            content = content.replace(bad, good)
            
        # Generic "buttدر" repair if missed
        content = content.replace('buttدر', 'button')
        content = content.replace('actiدر', 'action')
        content = content.replace('cدرtainer', 'container')
        content = content.replace('nدرe', 'none')
        content = content.replace('optiدر', 'option')
        content = content.replace('sectiدر', 'section')
        
        if content != original:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error processing {filepath}: {e}")
        return False

def main():
    print("=" * 70)
    print("REPAIRING CORRUPTED FILES ('on' -> 'در')")
    print("=" * 70)
    
    html_files = []
    for root, dirs, files in os.walk(BASE_DIR):
        for file in files:
            if file.endswith('.html'):
                html_files.append(os.path.join(root, file))
    
    print(f"Found {len(html_files)} HTML files")
    
    count = 0
    for filepath in html_files:
        if repair_files(filepath):
            count += 1
            print(f"Repaired: {os.path.basename(filepath)}")
            
    print("=" * 70)
    print(f"Complete: Repaired {count} files")
    print("=" * 70)

if __name__ == '__main__':
    main()





